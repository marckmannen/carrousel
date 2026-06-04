<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PharmacyApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected PharmacyApiService $pharmacyApi
    ) {}

    /**
     * List all orders for the authenticated user.
     */
    public function index(): View
    {
        $orders = auth()->user()->orders()->latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }

    /**
     * Store a new order.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|string',
            'amount' => 'required|integer|min:1|max:99',
            'birthdate' => 'required|date|before:today',
        ]);

        try {
            $product = $this->pharmacyApi->getProduct($validated['product_id']);

            if (!$product || !isset($product['id'])) {
                return back()
                    ->withInput()
                    ->withErrors(['product_id' => 'Dit product is niet gevonden of niet meer beschikbaar.']);
            }

            $order = $this->pharmacyApi->createOrder(
                auth()->id(),
                $validated['product_id'],
                $validated['amount'],
                $validated['birthdate']
            );

            return redirect()->route('orders.show', $order)
                ->with('success', 'Bestelling succesvol geplaatst! Houd uw pincode goed bij.');
        } catch (\Exception $e) {
            Log::error('Pharmacy API error in OrderController@store', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
            ]);

            return back()
                ->withInput()
                ->with('error', 'De apotheek is momenteel niet bereikbaar. Probeer het later opnieuw.');
        }
    }

    /**
     * Show a single order.
     */
    public function show(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Deze bestelling kan niet meer geannuleerd worden.');
        }

        $validated = $request->validate([
            'birthdate' => 'required|date',
            'pincode' => 'required|string|min:4',
        ]);

        try {
            $pharmacyId = $order->pharmacy_id ?? config('services.pharmacy.pharmacy_id');
            $response = $this->pharmacyApi->cancelOrderForPharmacy(
                $pharmacyId,
                $order->order_id,
                $validated['birthdate'],
                $validated['pincode']
            );

            $order->update([
                'status' => $response['status'] ?? 'cancelled',
                'api_response' => $response,
            ]);

            return back()->with('success', 'Bestelling succesvol geannuleerd.');
        } catch (\Exception $e) {
            Log::error('Pharmacy API error in OrderController@cancel', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Er ging iets mis bij het annuleren. Probeer het later opnieuw.');
        }
    }

    /**
     * Refresh order status from the API.
     */
    public function refresh(Order $order): RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $pharmacyId = $order->pharmacy_id ?? config('services.pharmacy.pharmacy_id');
            $response = $this->pharmacyApi->getOrderStatusForPharmacy($pharmacyId, $order->order_id);

            $order->update([
                'status' => $response['status'] ?? $order->status,
                'api_response' => $response,
            ]);

            return back()->with('success', 'Bestelstatus bijgewerkt.');
        } catch (\Exception $e) {
            Log::error('Pharmacy API error in OrderController@refresh', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'De apotheek is momenteel niet bereikbaar. Probeer het later opnieuw.');
        }
    }
}
