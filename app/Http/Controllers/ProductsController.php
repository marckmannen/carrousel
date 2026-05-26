<?php

namespace App\Http\Controllers;

use App\Services\PharmacyApiService;
use Illuminate\Http\Request;
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
        try {
            $products = $this->pharmacyApi->getProducts();
        } catch (\Exception $e) {
            $products = [];
        }

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

        try {
            $allProducts = $this->pharmacyApi->getProducts();
            $products = collect($allProducts)->filter(function ($product) use ($query) {
                return str_contains(strtolower($product['name'] ?? ''), strtolower($query));
            })->values()->all();
        } catch (\Exception $e) {
            $products = [];
        }

        return view('products.index', compact('products', 'query'));
    }
}
