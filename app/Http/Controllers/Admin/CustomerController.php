<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * List all customer (non-admin) accounts.
     */
    public function index(): View
    {
        $customers = User::where('role', '!=', 'admin')
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Klant succesvol aangemaakt.');
    }

    /**
     * Display the specified customer.
     */
    public function show(User $customer): View
    {
        if ($customer->isAdmin()) {
            abort(403);
        }

        $orders = $customer->orders()->latest()->get();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(User $customer): View
    {
        if ($customer->isAdmin()) {
            abort(403);
        }

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, User $customer): RedirectResponse
    {
        if ($customer->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $customer->id],
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Klant bijgewerkt.');
    }

    /**
     * Show the password reset form for a customer.
     */
    public function editPassword(User $customer): View
    {
        if ($customer->isAdmin()) {
            abort(403);
        }

        return view('admin.customers.password', compact('customer'));
    }

    /**
     * Update the customer's password.
     */
    public function updatePassword(Request $request, User $customer): RedirectResponse
    {
        if ($customer->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $customer->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Wachtwoord succesvol bijgewerkt.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(User $customer): RedirectResponse
    {
        if ($customer->isAdmin()) {
            abort(403);
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Klant verwijderd.');
    }
}
