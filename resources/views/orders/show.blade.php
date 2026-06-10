<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Bestelling #{{ $order->order_id }}
            </h2>
            <a href="{{ route('orders.index') }}"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Terug naar overzicht
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Order Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $order->product_name }}
                        </h3>
                        <p class="text-sm text-gray-500">Besteld op {{ $order->created_at->locale('nl')->isoFormat('DD-MM-YYYY om HH:mm') }}</p>
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
                                'ready' => 'Klaar voor ophalen',
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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hoeveelheid</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $order->amount }}×</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Product ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $order->product_id }}</dd>
                    </div>
                    @if($order->pincode)
                        <div x-data="{ show: false }">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pincode</dt>
                            <dd class="mt-1 flex items-center gap-2">
                                <span class="text-sm font-mono font-bold text-gray-900 dark:text-gray-100 text-lg" x-show="show">{{ $order->pincode }}</span>
                                <span class="text-sm font-mono font-bold text-gray-900 dark:text-gray-100 text-lg" x-show="!show">****</span>
                                <button type="button" @click="show = !show" class="text-xs text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <span x-text="show ? 'Verbergen' : 'Tonen'"></span>
                                </button>
                            </dd>
                        </div>
                    @endif
                </dl>

                <!-- Actions -->
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex gap-4">
                    <!-- Refresh Status -->
                    <form action="{{ route('orders.refresh', $order) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            ↻ Status vernieuwen
                        </button>
                    </form>

                    <!-- Cancel (only if pending) -->
                    @if($order->status === 'pending')
                        <button type="button" onclick="document.getElementById('cancelModal').showModal()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Bestelling annuleren
                        </button>
                    @endif
                </div>
            </div>

            <!-- Cancel Modal -->
            <dialog id="cancelModal" class="rounded-xl p-0 backdrop:bg-gray-900/50">
                <div class="bg-white dark:bg-gray-800 p-6 max-w-md w-full">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Bestelling annuleren
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Vul je geboortedatum of pincode in om de bestelling te annuleren.
                    </p>
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" class="mt-4">
                        @csrf
                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Geboortedatum
                            </label>
                            <input type="date" name="birthdate" id="birthdate"
                                value="{{ old('birthdate', $order->birthdate) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="mt-4">
                            <label for="pincode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pincode
                            </label>
                            <input type="password" name="pincode" id="pincode" minlength="4" maxlength="10"
                                placeholder="1234"
                                class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" onclick="document.getElementById('cancelModal').close()"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Annuleren
                            </button>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                Ja, bestelling annuleren
                            </button>
                        </div>
                    </form>
                </div>
            </dialog>
        </div>
    </div>
</x-app-layout>
