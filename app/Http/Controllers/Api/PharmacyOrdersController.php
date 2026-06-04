<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PharmacyOrdersController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        if (!$this->verifyAuth()) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Ongeldige of ontbrekende autorisatie.',
                ],
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => 'required|string',
            'amount' => 'required|integer|min:1|max:99',
            'birthdate' => 'required|date',
        ]);

        try {
            $product = Product::where('uuid', $validated['product_id'])->first();

            if (!$product) {
                return response()->json([
                    'error' => [
                        'code' => 'product_not_found',
                        'message' => 'Product niet gevonden.',
                        'details' => ['productId' => $validated['product_id']],
                    ],
                ], 404);
            }

            if ($product->stock < $validated['amount']) {
                return response()->json([
                    'error' => [
                        'code' => 'insufficient_stock',
                        'message' => 'Niet genoeg voorraad. Er zijn maar ' . $product->stock . ' ' . $product->name . ' op voorraad.',
                        'details' => ['productId' => $validated['product_id'], 'available' => $product->stock],
                    ],
                ], 400);
            }

            $user = User::where('birthdate', $validated['birthdate'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'Klant ' . date('Y-m-d', strtotime($validated['birthdate'])),
                    'email' => 'order-' . Str::random(20) . '@pharmacy.local',
                    'password' => bcrypt(Str::random(32)),
                    'role' => 'user',
                    'birthdate' => $validated['birthdate'],
                ]);
            }

            $product->decrement('stock', $validated['amount']);

            $orderId = 'ord_' . Str::random(12);
            $pincode = sprintf('%04d', random_int(1000, 9999));

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

            return response()->json([
                'order_id' => $order->order_id,
                'product_id' => $order->product_id,
                'amount' => $order->amount,
                'status' => $order->status,
                'pincode' => $order->pincode,
            ])->setStatusCode(201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PharmacyOrdersController@store error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Er ging iets mis met het plaatsen van de bestelling.',
                ],
            ], 503);
        }
    }

    public function show(string $orderId): JsonResponse
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
            $order = Order::where('order_id', $orderId)->first();

            if (!$order) {
                return response()->json([
                    'error' => [
                        'code' => 'order_not_found',
                        'message' => 'Bestelling niet gevonden.',
                        'details' => ['orderId' => $orderId],
                    ],
                ], 404);
            }

            return response()->json([
                'order_id' => $order->order_id,
                'product_id' => $order->product_id,
                'amount' => $order->amount,
                'status' => $order->status,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PharmacyOrdersController@show error', ['error' => $e->getMessage(), 'orderId' => $orderId]);
            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Er ging iets mis.',
                ],
            ], 503);
        }
    }

    public function cancel(Request $request, string $orderId): JsonResponse
    {
        if (!$this->verifyAuth()) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Ongeldige of ontbrekende autorisatie.',
                ],
            ], 401);
        }

        $validated = $request->validate([
            'birthdate' => 'required|date',
            'pincode' => 'required|string',
        ]);

        try {
            $order = Order::where('order_id', $orderId)->first();

            if (!$order) {
                return response()->json([
                    'error' => [
                        'code' => 'order_not_found',
                        'message' => 'Bestelling niet gevonden.',
                        'details' => ['orderId' => $orderId],
                    ],
                ], 404);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'error' => [
                        'code' => 'order_not_pending',
                        'message' => 'Deze bestelling kan niet meer geannuleerd worden.',
                        'details' => ['orderId' => $orderId, 'status' => $order->status],
                    ],
                ], 400);
            }

            if (!hash_equals($order->pincode, $validated['pincode'])) {
                return response()->json([
                    'error' => [
                        'code' => 'invalid_pincode',
                        'message' => 'Ongeldige pincode.',
                        'details' => ['orderId' => $orderId],
                    ],
                ], 400);
            }

            $product = Product::where('uuid', $order->product_id)->first();
            if ($product) {
                $product->increment('stock', $order->amount);
            }

            $order->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'order_id' => $order->order_id,
                'product_id' => $order->product_id,
                'amount' => $order->amount,
                'status' => $order->status,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PharmacyOrdersController@cancel error', ['error' => $e->getMessage(), 'orderId' => $orderId]);
            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Er ging iets mis met het annuleren van de bestelling.',
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

    protected function resolveProductName(string $productId): string
    {
        $product = Product::where('uuid', $productId)->first();
        if ($product) {
            return $product->name;
        }

        $product = Product::where('id', $productId)->first();
        if ($product) {
            return $product->name;
        }

        return "Product {$productId}";
    }
}
