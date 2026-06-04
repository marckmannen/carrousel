<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:png,jpeg,webp', 'max:5120'],
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('medicines', 'public');
            $imageUrl = '/storage/' . $path;
        }

        Product::create([
            'name' => $validated['name'],
            'shortDescription' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'imageUrl' => $imageUrl,
            'stock' => $validated['stock'],
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Medicijn succesvol aangemaakt.');
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:png,jpeg,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->imageUrl) {
                $oldPath = str_starts_with($product->imageUrl, '/storage/')
                    ? substr($product->imageUrl, strlen('/storage/'))
                    : null;
                if ($oldPath) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('image')->store('medicines', 'public');
            $validated['imageUrl'] = '/storage/' . $path;
        }

        $product->update([
            'name' => $validated['name'],
            'shortDescription' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'stock' => $validated['stock'],
            'imageUrl' => $validated['imageUrl'] ?? $product->imageUrl,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Medicijn bijgewerkt.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->imageUrl) {
            $path = str_starts_with($product->imageUrl, '/storage/')
                ? substr($product->imageUrl, strlen('/storage/'))
                : null;
            if ($path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Medicijn verwijderd.');
    }
}
