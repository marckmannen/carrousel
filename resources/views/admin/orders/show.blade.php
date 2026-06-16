<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Bestelling #{{ $order->order_id }}
            </h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                ← Terug naar overzicht
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $order->product_name }}
                        </h3>
                        <p class="text-sm text-gray-500">Besteld op {{ $order->created_at->locale('nl')->isoFormat('DD-MM-YYYY HH:mm') }}</p>
                    </div>
                    <div>
                        @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'ready' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'rejected' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $statusLabels = [
                                'pending' => 'In afwachting',
                                'ready' => 'Klaar',
                                'cancelled' => 'Geannuleerd',
                                'rejected' => 'Afgekeurd',
                            ];
                        @endphp
                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                            {{ $statusClasses[$order->status] ?? $statusClasses['pending'] }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Order ID</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $order->order_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Klant</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $order->user ? $order->user->name : 'Systeem' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hoeveelheid</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $order->amount }}×</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Product ID</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $order->product_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Geboortedatum</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $order->birthdate ? $order->birthdate->locale('nl')->isoFormat('DD-MM-YYYY') : '-' }}</dd>
                    </div>
                    @if($order->pincode)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pincode</dt>
                            <dd class="mt-1 text-sm font-mono font-bold text-gray-900 dark:text-gray-100">{{ $order->pincode }}</dd>
                        </div>

                        @if(in_array($order->status, ['pending', 'ready']))
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">QR-code</dt>
                                <dd class="mt-2">
                                    <div class="inline-block bg-white p-3 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                        @php
                                            $options = new \chillerlan\QRCode\QROptions(['outputBase64' => true, 'drawWide' => false, 'margin' => 4, 'size' => 180]);
                                            $qr = new \chillerlan\QRCode\QRCode($options);
                                            $qrImage = $qr->render($order->pincode);
                                        @endphp
                                        <img src="{{ $qrImage }}" alt="QR-code pincode {{ $order->pincode }}" class="w-44 h-44"
                                    </div>
                                </dd>
                            </div>
                        @endif
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
