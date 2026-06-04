<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PharmacyApiService
{
    protected string $baseUrl;
    protected ?string $pharmacyId;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.pharmacy.api_url');
        $this->pharmacyId = config('services.pharmacy.pharmacy_id');
        $this->timeout = (int) env('PHARMACY_REQUEST_TIMEOUT_MS', 4000);
    }

    public function getProducts(): array
    {
        return $this->request('GET', "/pharmacies/{$this->pharmacyId}/products");
    }

    public function getProduct(string $productId): ?array
    {
        return $this->request('GET', "/pharmacies/{$this->pharmacyId}/products/{$productId}");
    }

    public function getAllPharmacyProducts(): array
    {
        return $this->request('GET', '/pharmacies/products');
    }

    public function getOrderStatusForPharmacy(string $pharmacyId, string $orderId): array
    {
        return $this->request('GET', "/pharmacies/{$pharmacyId}/orders/{$orderId}");
    }

    public function cancelOrderForPharmacy(string $pharmacyId, string $orderId, string $birthdate, string $pincode): array
    {
        return $this->request('POST', "/pharmacies/{$pharmacyId}/orders/{$orderId}/cancel", [
            'birthdate' => $birthdate,
            'pincode' => $pincode,
        ]);
    }

    /**
     * Create an order by posting to the central API.
     * The central API will then POST to our /api/orders which creates the order in our DB.
     * Returns the API response and the corresponding DB Order if found.
     */
    public function createOrder(int $userId, string $productId, int $amount, string $birthdate): array
    {
        $response = $this->request('POST', "/pharmacies/{$this->pharmacyId}/orders", [
            'product_id' => $productId,
            'amount' => $amount,
            'birthdate' => $birthdate,
        ]);

        return $response;
    }

    public function getOrderStatus(string $orderId): array
    {
        return $this->request('GET', "/pharmacies/{$this->pharmacyId}/orders/{$orderId}");
    }

    public function cancelOrder(string $orderId, string $birthdate, string $pincode): array
    {
        return $this->request('POST', "/pharmacies/{$this->pharmacyId}/orders/{$orderId}/cancel", [
            'birthdate' => $birthdate,
            'pincode' => $pincode,
        ]);
    }

    public function isOnline(): bool
    {
        try {
            $response = Http::timeout(10)
                ->get($this->baseUrl . '/pharmacies/' . $this->pharmacyId);
            return (bool) ($response->json()['online'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout($this->timeout / 1000)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->when($data, fn ($request) => $request->contentType('application/json'))
                ->send(
                    $method,
                    $this->baseUrl . $endpoint,
                    $data ? ['json' => $data] : []
                );

            if (!$response->successful()) {
                $body = $response->json();
                throw new Exception(
                    $body['error']['message'] ?? "API error: " . $response->status(),
                    $response->status()
                );
            }

            return $response->json();
        } catch (RequestException $e) {
            Log::error('Pharmacy API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('De apotheek is momenteel niet bereikbaar. Probeer het later opnieuw.', 0, $e);
        }
    }
}
