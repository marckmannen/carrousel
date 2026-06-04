<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalOrderController extends Controller
{
    protected function baseUrl(): string
    {
        return config('services.pharmacy.api_url');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pharmacy_id' => 'required|string',
            'product_id' => 'required|string',
            'amount' => 'required|integer|min:1|max:99',
            'birthdate' => 'required|date|before:today',
        ]);

        try {
            $response = Http::timeout(10)
                ->contentType('application/json')
                ->post($this->baseUrl() . "/pharmacies/{$validated['pharmacy_id']}/orders", [
                    'product_id' => $validated['product_id'],
                    'amount' => $validated['amount'],
                    'birthdate' => $validated['birthdate'],
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                return back()
                    ->withInput()
                    ->with('error', $body['error']['message'] ?? 'Er ging iets mis bij het plaatsen van de bestelling.');
            }

            $data = $response->json();

            $productName = $this->resolveExternalProductName($validated['pharmacy_id'], $validated['product_id']);

            $order = Order::create([
                'user_id' => auth()->id(),
                'pharmacy_id' => $validated['pharmacy_id'],
                'order_id' => $data['order_id'] ?? null,
                'product_id' => $validated['product_id'],
                'product_name' => $productName,
                'amount' => $validated['amount'],
                'status' => $data['status'] ?? 'pending',
                'pincode' => $data['pincode'] ?? null,
                'birthdate' => $validated['birthdate'],
                'api_response' => $data,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Bestelling succesvol geplaatst bij externe apotheek! Houd uw pincode goed bij.');

        } catch (Exception $e) {
            Log::error('External order creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'pharmacy_id' => $validated['pharmacy_id'],
                'product_id' => $validated['product_id'],
            ]);

            return back()
                ->withInput()
                ->with('error', 'De apotheek is momenteel niet bereikbaar. Probeer het later opnieuw.');
        }
    }

    protected function resolveExternalProductName(string $pharmacyId, string $productId): string
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl() . "/pharmacies/{$pharmacyId}/products/{$productId}");
            if ($response->successful()) {
                $product = $response->json();
                return $product['name'] ?? "Product {$productId}";
            }
        } catch (Exception) {
            // Fallback handled below
        }
        return "Product {$productId}";
    }
}
