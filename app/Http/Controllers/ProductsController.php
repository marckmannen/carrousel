<?php

namespace App\Http\Controllers;

use App\Services\PharmacyApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductsController extends Controller
{
    public function __construct(
        protected PharmacyApiService $pharmacyApi
    ) {}

    /**
     * Display all products from the pharmacy API.
     */
    public function index(): View
    {
        $products = $this->getProductsFromCache();

        return view('products.index', compact('products'));
    }

    /**
     * Display a single product with an order form.
     */
    public function show(string $productId): View
    {
        try {
            $product = $this->pharmacyApi->getProduct($productId);
        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Product niet gevonden of niet meer beschikbaar.');
        }

        return view('products.show', compact('product'));
    }

    /**
     * Search products by name.
     */
    public function search(Request $request): View
    {
        $query = $request->input('q', '');
        $products = $this->getProductsFromCache();

        if (!empty($query)) {
            $products = collect($products)->filter(function ($product) use ($query) {
                return str_contains(strtolower($product['name'] ?? ''), strtolower($query));
            })->values()->all();
        }

        return view('products.index', compact('products', 'query'));
    }

    /**
     * Get products with caching to avoid repeated API calls.
     */
    protected function getProductsFromCache(): array
    {
        try {
            return Cache::remember('pharmacy.products', now()->addMinutes(15), function () {
                return $this->pharmacyApi->getProducts();
            });
        } catch (\Exception) {
            return [];
        }
    }
}
