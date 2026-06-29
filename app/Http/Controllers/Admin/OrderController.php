<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with('user')->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,ready,completed,cancelled,rejected'],
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Release pincode when order is cancelled or rejected
        if (in_array($oldStatus, ['pending', 'ready']) && in_array($validated['status'], ['cancelled', 'rejected'])) {
            $order->releasePincode();
        }

        return redirect()->back()->with('success', 'Status bijgewerkt naar "' . $this->statusLabel($validated['status']) . '".');
    }

    protected function statusLabel(string $status): string
    {
        $labels = [
            'pending' => 'In afwachting',
            'ready' => 'Klaar voor ophalen',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
            'rejected' => 'Afgekeurd',
        ];

        return $labels[$status] ?? $status;
    }
}
