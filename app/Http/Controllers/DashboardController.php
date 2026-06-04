<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PharmacyApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PharmacyApiService $pharmacyApi
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $data = [
            'isAdmin' => $user->isAdmin(),
            'user' => $user,
        ];

        if ($user->isAdmin()) {
            $data['customerCount'] = User::where('role', '!=', 'admin')->count();
            $data['orderCount'] = Order::count();
            $data['productCount'] = Product::count();

            try {
                $data['apiOnline'] = $this->pharmacyApi->isOnline();
            } catch (\Throwable) {
                $data['apiOnline'] = false;
            }
        } else {
            $data['recentOrders'] = $user->orders()->latest()->limit(5)->get();
        }

        return view('dashboard', $data);
    }
}
