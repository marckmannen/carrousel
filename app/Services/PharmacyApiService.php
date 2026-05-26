<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PharmacyApiService
{
    /**
     * Base URL for the pharmacy API.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * The pharmacy ID for this pharmacy.
     *
     * @var string
     */
    protected string $pharmacyId;

    /**
     * Timeout for API requests in milliseconds.
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Create a new PharmacyApiService instance.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.pharmacy.api_url');
        $this->pharmacyId = config('services.pharmacy.pharmacy_id');
        $this->timeout = (int) env('PHARMACY_REQUEST_TIMEOUT_MS', 4000);
    }

    /**
     * Get all products from this pharmacy via the central API.
     *
     * @return array
     * @throws Exception
     */
    public function getProducts(): array
    {
        return $this->request('GET', "/pharmacies/{$this->pharmacyId}/products");
    }

    /**
     * Get a single product from this pharmacy.
     *
     * @param string $productId
     * @return array|null
     * @throws Exception
     */
    public function getProduct(string $productId): ?array
    {
        return $this->request('GET', "/pharmacies/{$this->pharmacyId}/products/{$productId}");
    }

    /**
     * Get all products from all pharmacies (overview).
     *
     * @return array
     * @throws Exception
     */
    public function getAllPharmacyProducts(): array
    {
        return $this->request('GET', '/pharmacies/products');
    }

    /**
     * Create an order at this pharmacy.
     *
     * @param int $userId
     * @param string $productId
     * @param int $amount
     * @param string $birthdate
     * @return Order
     * @throws Exception
     */
    public function createOrder(int $userId, string $productId, int $amount, string $birthdate): Order
    {
        $response = $this->request('POST', "/pharmacies/{$this->pharmacyId}/orders", [
            'product_id' => $productId,
            'amount' => $amount,
            'birthdate' => $birthdate,
        ]);

        // Get product name for the display
        $productName = $this->resolveProductName($productId);

        // Persist the order locally
        $order = Order::create([
            'user_id' => $userId,
            'order_id' => $response['order_id'] ?? null,
            'product_id' => $productId,
            'product_name' => $productName,
            'amount' => $amount,
            'status' => $response['status'] ?? 'pending',
            'pincode' => $response['pincode'] ?? null,
            'birthdate' => $birthdate,
            'api_response' => $response,
        ]);

        return $order;
    }

    /**
     * Get the status of an order from the API.
     *
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function getOrderStatus(string $orderId): array
    {
        return $this->request('GET', "/pharmacies/{$this->pharmacyId}/orders/{$orderId}");
    }

    /**
     * Cancel an order at the pharmacy.
     *
     * @param string $orderId
     * @param string $birthdate
     * @param string $pincode
     * @return array
     * @throws Exception
     */
    public function cancelOrder(string $orderId, string $birthdate, string $pincode): array
    {
        return $this->request('POST', "/pharmacies/{$this->pharmacyId}/orders/{$orderId}/cancel", [
            'birthdate' => $birthdate,
            'pincode' => $pincode,
        ]);
    }

    /**
     * Check if this pharmacy is online by querying the central API.
     *
     * @return bool
     */
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

    /**
     * Resolve product name from API.
     *
     * @param string $productId
     * @return string
     */
    protected function resolveProductName(string $productId): string
    {
        try {
            $product = $this->getProduct($productId);
            return $product['name'] ?? $product['name'] ?? "Product {$productId}";
        } catch (Exception) {
            return "Product {$productId}";
        }
    }

    /**
     * Make an API request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws Exception
     */
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
