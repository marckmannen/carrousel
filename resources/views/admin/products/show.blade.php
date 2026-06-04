<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Medicijn bekijken') }}
            </h2>
            <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                ← Terug naar overzicht
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($product->imageUrl)
                    <div class="mb-6">
                        <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="max-h-64 rounded-lg object-contain bg-gray-100 dark:bg-gray-700">
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Naam</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                    </div>

                    @if($product->shortDescription)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Korte beschrijving</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->shortDescription }}</p>
                        </div>
                    @endif

                    @if($product->description)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Volledige beschrijving</h3>
                            <div class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $product->description }}</div>
                        </div>
                    @endif

                <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Voorraad</h3>
                        <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->stock }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Product ID</h3>
                        <p class="mt-1 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $product->uuid }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Toegevoegd op</h3>
                        <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->created_at->locale('nl')->isoFormat('DD-MM-YYYY HH:mm') }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6 space-x-3">
                    <a href="{{ route('admin.products.edit', $product) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Bewerken
                    </a>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je dit medicijn wilt verwijderen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                            Verwijderen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
