<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PharmacyApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $product = Product::where('uuid', $validated['product_id'])->first();

            if (!$product) {
                return back()
                    ->withInput()
                    ->withErrors(['product_id' => 'Dit product is niet gevonden of niet meer beschikbaar.']);
            }

            if ($product->stock < $validated['amount']) {
                return back()
                    ->withInput()
                    ->with('error', 'Niet genoeg voorraad beschikbaar. Er zijn maar ' . $product->stock . ' op voorraad.');
            }

            $user = auth()->user();

            if (!$user->birthdate) {
                $user->update(['birthdate' => $validated['birthdate']]);
            }

            $orderId = 'ord_' . Str::random(12);
            $pincode = sprintf('%04d', random_int(1000, 9999));

            $product->decrement('stock', $validated['amount']);

            $order = Order::create([
                'user_id' => $user->id,
                'pharmacy_id' => config('services.pharmacy.pharmacy_id'),
                'order_id' => $orderId,
                'product_id' => $validated['product_id'],
                'product_name' => $product->name,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'pincode' => $pincode,
                'birthdate' => $validated['birthdate'],
                'api_response' => null,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Bestelling succesvol geplaatst! Houd uw pincode goed bij.');
        } catch (\Exception $e) {
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
            'birthdate' => 'required|date',
            'pincode' => 'required|string|min:4',
        ]);

        if (!hash_equals($order->pincode, $validated['pincode'])) {
            return back()->with('error', 'Ongeldige pincode.');
        }

        $product = Product::where('uuid', $order->product_id)->first();
        if ($product) {
            $product->increment('stock', $order->amount);
        }

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
