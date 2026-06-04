<?php

use App\Http\Controllers\Api\PharmacyOrdersController;
use App\Http\Controllers\Api\PharmacyProductsController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExternalOrderController;
use App\Http\Controllers\ExternalPharmaciesController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::get('api/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()->toDateTimeString()]);
})->name('api.health');

// API: Central server calls these endpoints on our pharmacy
Route::get('api/products', [PharmacyProductsController::class, 'index'])->name('api.products.index');
Route::get('api/products/{productId}', [PharmacyProductsController::class, 'show'])->name('api.products.show');
Route::post('api/orders', [PharmacyOrdersController::class, 'store'])->name('api.orders.store');
Route::get('api/orders/{orderId}', [PharmacyOrdersController::class, 'show'])->name('api.orders.show');
Route::post('api/orders/{orderId}/cancel', [PharmacyOrdersController::class, 'cancel'])->name('api.orders.cancel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Products
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/products/search', [ProductsController::class, 'search'])->name('products.search');
    Route::get('/products/{productId}', [ProductsController::class, 'show'])->name('products.show');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/refresh', [OrderController::class, 'refresh'])->name('orders.refresh');

    // External pharmacies
    Route::get('/external', [ExternalPharmaciesController::class, 'index'])->name('external.index');
    Route::get('/external/{pharmacyId}', [ExternalPharmaciesController::class, 'show'])->name('external.show');
    Route::get('/external/{pharmacyId}/products/{productId}', [ExternalPharmaciesController::class, 'product'])->name('external.product');
    Route::post('/external/orders', [ExternalOrderController::class, 'store'])->name('external.orders.store');

    // Admin routes
    Route::middleware('can:admin')->group(function () {
        Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
        Route::get('/admin/customers/create', [CustomerController::class, 'create'])->name('admin.customers.create');
        Route::post('/admin/customers', [CustomerController::class, 'store'])->name('admin.customers.store');
        Route::get('/admin/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
        Route::get('/admin/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('admin.customers.edit');
        Route::patch('/admin/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
        Route::delete('/admin/customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');
        Route::get('/admin/customers/{customer}/password', [CustomerController::class, 'editPassword'])->name('admin.customers.password.edit');
        Route::patch('/admin/customers/{customer}/password', [CustomerController::class, 'updatePassword'])->name('admin.customers.password.update');

        Route::get('/admin/products', [AdminProductController::class, 'index'])->name('admin.products.index');
        Route::get('/admin/products/create', [AdminProductController::class, 'create'])->name('admin.products.create');
        Route::post('/admin/products', [AdminProductController::class, 'store'])->name('admin.products.store');
        Route::get('/admin/products/{product}', [AdminProductController::class, 'show'])->name('admin.products.show');
        Route::get('/admin/products/{product}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
        Route::patch('/admin/products/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/admin/products/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
    });
});

require __DIR__.'/auth.php';
