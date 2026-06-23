<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ExternalPharmaciesController extends Controller
{
    protected function baseUrl(): string
    {
        return config('services.pharmacy.api_url');
    }

    public function index(): View
    {
        $pharmacies = $this->fetchPharmaciesWithProducts();

        return view('external.index', compact('pharmacies'));
    }

    public function show(Request $request, string $pharmacyId): View
    {
        try {
            $pharmacy = json_decode(Http::timeout(5)->get($this->baseUrl() . "/pharmacies/{$pharmacyId}")->body(), true);
        } catch (Exception $e) {
            Log::error('External pharmacy not found', ['pharmacyId' => $pharmacyId, 'error' => $e->getMessage()]);
            return redirect()->route('external.index')
                ->with('error', 'Apotheek niet gevonden.');
        }

        if (!$pharmacy || !$pharmacy['online']) {
            return redirect()->route('external.index')
                ->with('error', 'Deze apotheek is momenteel offline.');
        }

        $products = $this->fetchPharmacyProducts($pharmacyId);

        $query = $request->input('q', '');

        if ($query) {
            $products = collect($products)->filter(function ($product) use ($query) {
                return str_contains(strtolower($product['name'] ?? ''), strtolower($query));
            })->values()->all();
        }

        return view('external.show', compact('pharmacy', 'products', 'query'));
    }

    public function product(string $pharmacyId, string $productId): View
    {
        try {
            $pharmacy = json_decode(Http::timeout(5)->get($this->baseUrl() . "/pharmacies/{$pharmacyId}")->body(), true);
        } catch (Exception $e) {
            return redirect()->route('external.index')
                ->with('error', 'Apotheek niet gevonden.');
        }

        try {
            $product = json_decode(
                Http::timeout(5)->get($this->baseUrl() . "/pharmacies/{$pharmacyId}/products/{$productId}")->body(),
                true
            );
        } catch (Exception $e) {
            Log::error('External product not found', ['pharmacyId' => $pharmacyId, 'productId' => $productId, 'error' => $e->getMessage()]);
            return redirect()->route('external.show', $pharmacyId)
                ->with('error', 'Product niet gevonden of niet meer beschikbaar.');
        }

        if (!$product) {
            return redirect()->route('external.show', $pharmacyId)
                ->with('error', 'Product niet gevonden.');
        }

        return view('external.product', compact('pharmacy', 'product'));
    }

    protected function fetchPharmaciesWithProducts(): array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl() . '/pharmacies/products');
            if (!$response->successful()) {
                return [];
            }
            $data = $response->json();
            $ourPharmacyId = config('services.pharmacy.pharmacy_id');
            $excludedCodes = ['5306']; // dubbele Carrousel-apotheek (niet in gebruik)

            $pharmacyList = collect($data)->map(fn($i) => sprintf("%s (%s)", $i['pharmacy']['name'] ?? 'unknown', $i['pharmacy']['id'] ?? 'N/A'))->join(', ');

            Log::info('Pharmacy filter status', [
                'ourPharmacyId' => $ourPharmacyId,
                'availablePharmacies' => $pharmacyList,
            ]);

            return collect($data)
                ->filter(function ($item) use ($ourPharmacyId, $excludedCodes) {
                    $theirId = (string) ($item['pharmacy']['id'] ?? '');
                    $theirCode = (string) ($item['pharmacy']['pharmacyCode'] ?? '');
                    $isOnline = $item['pharmacy']['online'] ?? $item['online'] ?? false;

                    if (!$isOnline) {
                        return false;
                    }
                    if ($ourPharmacyId && $theirId === (string) $ourPharmacyId) {
                        return false;
                    }
                    if (in_array($theirCode, $excludedCodes)) {
                        return false;
                    }

                    return true;
                })
                ->values()
                ->all();
        } catch (Exception $e) {
            Log::error('Failed to fetch external pharmacies', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function fetchPharmacyProducts(string $pharmacyId): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl() . "/pharmacies/{$pharmacyId}/products");
            if (!$response->successful()) {
                return [];
            }
            return (array) $response->json();
        } catch (Exception $e) {
            Log::error('Failed to fetch pharmacy products', ['pharmacyId' => $pharmacyId, 'error' => $e->getMessage()]);
            return [];
        }
    }
}
