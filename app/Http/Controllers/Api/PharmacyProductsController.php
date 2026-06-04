<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class PharmacyProductsController extends Controller
{
    public function index(): JsonResponse
    {
        if (!$this->verifyAuth()) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Ongeldige of ontbrekende autorisatie.',
                ],
            ], 401);
        }

        try {
            $products = Product::all()->map(function ($product) {
                return [
                    'id' => $product->uuid,
                    'name' => $product->name,
                    'stock' => $product->stock,
                ];
            });

            return response()->json($products);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PharmacyProductsController@index error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Er ging iets mis.',
                ],
            ], 503);
        }
    }

    public function show(string $productId): JsonResponse
    {
        if (!$this->verifyAuth()) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Ongeldige of ontbrekende autorisatie.',
                ],
            ], 401);
        }

        try {
            $product = Product::where('uuid', $productId)->first();

            if (!$product) {
                return response()->json([
                    'error' => [
                        'code' => 'product_not_found',
                        'message' => 'Product niet gevonden.',
                        'details' => ['productId' => $productId],
                    ],
                ], 404);
            }

            return response()->json([
                'id' => $product->uuid,
                'name' => $product->name,
                'stock' => $product->stock,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PharmacyProductsController@show error', ['error' => $e->getMessage(), 'productId' => $productId]);
            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Er ging iets mis.',
                ],
            ], 503);
        }
    }

    protected function verifyAuth(): bool
    {
        $secret = config('services.pharmacy.secret_key') ?? env('PHARMACY_SECRET_KEY');
        $header = request()->header('Authorization');

        if (!$header || !$secret) {
            return false;
        }

        return preg_match('/^Bearer\s+(.+)$/i', $header, $matches) && hash_equals($secret, $matches[1]);
    }
}
