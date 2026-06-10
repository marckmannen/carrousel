<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PharmacyApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected PharmacyApiService $pharmacyApi
    ) {}

    public function index(): View
    {
        $orders = auth()->user()->orders()->latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }

    /**
     * Store a new order directly (user is logged in, no central API roundtrip needed).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|string',
            'amount' => 'required|integer|min:1|max:99',
            'birthdate' => 'required|date|before:today',
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                $product = Product::where('uuid', $validated['product_id'])->first();

                if (!$product) {
                    throw new \RuntimeException('product_not_found');
                }

                if ($product->stock < $validated['amount']) {
                    throw new \RuntimeException('insufficient_stock');
                }

                $user = auth()->user();

                if (!$user->birthdate) {
                    $user->update(['birthdate' => $validated['birthdate']]);
                }

                // Get or create birthday reference
                $birthday = \App\Models\Birthday::getOrCreate($validated['birthdate']);

                // Get available pincode and claim it
                $pincode = \App\Models\Pincode::getAvailable();
                $pincode->claim();

                $product->decrement('stock', $validated['amount']);

                $order = Order::create([
                    'user_id' => $user->id,
                    'pharmacy_id' => config('services.pharmacy.pharmacy_id'),
                    'order_id' => null,
                    'product_id' => $validated['product_id'],
                    'product_name' => $product->name,
                    'amount' => $validated['amount'],
                    'status' => 'pending',
                    'pincode' => $pincode->code,
                    'birthdate' => $validated['birthdate'],
                    'birthday_id' => $birthday->id,
                    'pincode_id' => $pincode->id,
                    'api_response' => null,
                ]);

                return $order;
            });

            return redirect()->route('orders.show', $order)
                ->with('success', 'Bestelling succesvol geplaatst! Houd uw pincode goed bij.');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'product_not_found') {
                return back()
                    ->withInput()
                    ->withErrors(['product_id' => 'Dit product is niet gevonden of niet meer beschikbaar.']);
            }

            if ($e->getMessage() === 'insufficient_stock') {
                return back()
                    ->withInput()
                    ->with('error', 'Niet genoeg voorraad beschikbaar.');
            }

            Log::error('OrderController@store error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
            ]);

            return back()
                ->withInput()
                ->with('error', 'Er ging iets mis bij het plaatsen van de bestelling. Probeer het later opnieuw.');
        }
    }

    public function show(Order $order): View|RedirectResponse
    {
        if (auth()->user()->isAdmin()) {
            return view('orders.show', compact('order'));
        }

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel an order (local status update).
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        if (!auth()->user()->isAdmin() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Deze bestelling kan niet meer geannuleerd worden.');
        }

        $validated = $request->validate([
            'pincode' => 'required_without:birthdate|string|min:4',
            'birthdate' => 'required_without:pincode|date',
        ]);

        // At least one of pincode or birthdate must be provided
        if (empty($validated['pincode']) && empty($validated['birthdate'])) {
            return back()->with('error', 'Vul je pincode of geboortedatum in om door te gaan.');
        }

        // Validate at least one of pincode or birthdate matches
        $pincodeMatches = isset($validated['pincode']) && hash_equals($order->pincode, $validated['pincode']);
        $birthdateMatches = isset($validated['birthdate']) && $order->birthdate && $order->birthdate->format('Y-m-d') === $validated['birthdate'];

        if (!$pincodeMatches && !$birthdateMatches) {
            return back()->with('error', 'Ongeldige pincode of geboortedatum.');
        }

        $product = Product::where('uuid', $order->product_id)->first();
        if ($product) {
            $product->increment('stock', $order->amount);
        }

        // Release the pincode so it can be reused
        $order->releasePincode();

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Bestelling succesvol geannuleerd. Voorraad is hersteld.');
    }

    public function refresh(Order $order): RedirectResponse
    {
        if (!auth()->user()->isAdmin() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        return back()->with('success', 'Bestelstatus is bijgewerkt.');
    }
}
