<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $product['name'] ?? 'Medicijn' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Via {{ $pharmacy['name'] }}</p>
            </div>
            <a href="{{ route('external.show', $pharmacy['id']) }}"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Terug naar overzicht
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(old())
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Controleer onderstaand formulier.</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $product['name'] ?? 'Medicijn' }}
                        </h1>

                        <div class="mt-6 flex items-center gap-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                ✓ Voorraad: {{ $product['stock'] ?? 'Niet beschikbaar' }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Apotheek: {{ $pharmacy['name'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bestellen</h3>

                        <form action="{{ route('external.orders.store') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="pharmacy_id" value="{{ $pharmacy['id'] }}">
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">

                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Hoeveelheid
                                </label>
                                <input type="number" name="amount" id="amount" value="{{ old('amount', 1) }}" min="1" max="99" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <label for="birthdate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Geboortedatum
                                </label>
                                <input type="date" name="birthdate" id="birthdate" value="{{ old('birthdate') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @error('birthdate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Vereist voor de bestelling bij de apotheek.</p>
                            </div>

                            @error('product_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @error('pharmacy_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                class="mt-6 w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Bestelling plaatsen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
