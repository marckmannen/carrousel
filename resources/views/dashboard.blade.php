<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            @if($isAdmin)
                {{ __('Apotheek Dashboard') }}
            @else
                {{ __('Welkom, ') }}{{ $user->name }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($isAdmin)
                <!-- Admin Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Totaal klanten</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $customerCount }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Totaal bestellingen</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $orderCount }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Totaal medicijnen</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $productCount }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Snelmenu</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('admin.customers.index') }}" class="flex items-center p-4 border border-transparent border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <span class="text-2xl mr-3">👥</span>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">Klanten beheren</div>
                                <div class="text-sm text-gray-500">Nieuwe klanten toevoegen en bewerken</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <span class="text-2xl mr-3">💊</span>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">Medicijnen beheren</div>
                                <div class="text-sm text-gray-500">Medicijnen toevoegen, bewerken en bekijken</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <span class="text-2xl mr-3">📦</span>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">Bestellingen bekijken</div>
                                <div class="text-sm text-gray-500">Alle bestellingen in de apotheek</div>
                            </div>
                        </a>
                    </div>
                </div>
                        </a>
                        <a href="{{ route('products.index') }}" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <span class="text-2xl mr-3">💊</span>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">Medicijnen bekijken</div>
                                <div class="text-sm text-gray-500">Alle beschikbare medicijnen uit de apotheek</div>
                            </div>
                        </a>
                    </div>
                </div>
            @else
                <!-- Customer Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Snelmenu</h3>
                        <div class="space-y-3">
                            <a href="{{ route('products.index') }}" class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <span class="text-xl mr-3">💊</span>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">Medicijnen bekijken</div>
                                    <div class="text-sm text-gray-500">Bekijk en bestel beschikbare medicijnen</div>
                                </div>
                            </a>
                            <a href="{{ route('orders.index') }}" class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <span class="text-xl mr-3">📦</span>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">Mijn bestellingen</div>
                                    <div class="text-sm text-gray-500">Bekijk de status van je bestellingen</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recente bestellingen</h3>
                        @if($recentOrders->isEmpty())
                            <p class="text-gray-500">Nog geen bestellingen geplaatst.</p>
                        @else
                            @foreach($recentOrders as $order)
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $order->product_name }}</div>
                                        <div class="text-xs text-gray-500">×{{ $order->amount }} - {{ $order->created_at->locale('nl')->isoFormat('DD-MM-YYYY') }}</div>
                                    </div>
                                    @php
                                        $badge = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'ready' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'rejected' => 'bg-gray-100 text-gray-800',
                                        ][$order->status] ?? 'bg-gray-100 text-gray-800';
                                        $label = [
                                            'pending' => 'In afwachting',
                                            'ready' => 'Klaar',
                                            'cancelled' => 'Geannuleerd',
                                            'rejected' => 'Afgekeurd',
                                        ][$order->status] ?? $order->status;
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $badge }}">
                                        {{ $label }}
                                    </span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
