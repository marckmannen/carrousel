<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Medicijnen') }}
            </h2>
            <form action="{{ route('products.search') }}" method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ $query ?? '' }}"
                    placeholder="Zoek medicijn..."
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Zoeken
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(empty($products))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        @isset($query)
                            Geen medicijnen gevonden voor "{{ $query }}".
                        @else
                            Geen medicijnen beschikbaar. De apotheek is mogelijk offline.
                        @endif
                    </p>

                    @empty($query)
                        <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-900 mt-2 inline-block">
                            Opnieuw proberen
                        </a>
                    @endempty
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('products.show', $product['id']) }}" class="hover:text-indigo-600">
                                        {{ $product['name'] ?? 'Onbekend medicijn' }}
                                    </a>
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $product['shortDescription'] ?? '' }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Opvoorraad: {{ $product['stock'] ?? '?' }}
                                    </span>
                                    <a href="{{ route('products.show', $product['id']) }}"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Bestellen →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
