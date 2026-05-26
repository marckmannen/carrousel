<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PharmacyApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'product_name' => 'required|string',
            'amount' => 'required|integer|min:1|max:99',
            'birthdate' => 'required|date|before:today',
        ]);

        try {
            $order = $this->pharmacyApi->createOrder(
                auth()->id(),
                $validated['product_id'],
                $validated['amount'],
                $validated['birthdate']
            );

            return redirect()->route('orders.show', $order)
                ->with('success', 'Bestelling succesvol geplaatst! Houd uw pincode goed bij.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
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
            $response = $this->pharmacyApi->cancelOrder(
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
            return back()->with('error', $e->getMessage());
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
            $response = $this->pharmacyApi->getOrderStatus($order->order_id);

            $order->update([
                'status' => $response['status'] ?? $order->status,
                'api_response' => $response,
            ]);

            return back()->with('success', 'Bestelstatus bijgewerkt.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
