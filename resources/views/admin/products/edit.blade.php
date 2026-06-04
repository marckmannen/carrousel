<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Medicijn bewerken') }}
            </h2>
            <a href="{{ route('admin.products.show', $product) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                ← Terug
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Naam')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $product->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="short_description" :value="__('Korte beschrijving')" />
                        <x-text-input id="short_description" class="block mt-1 w-full" type="text" name="short_description" :value="old('short_description', $product->shortDescription)" />
                        <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="description" :value="__('Volledige beschrijving')" />
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="stock" :value="__('Voorraad')" />
                        <x-text-input id="stock" class="block mt-1 w-full" type="number" name="stock" :value="old('stock', $product->stock)" required min="0" />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    @if($product->imageUrl)
                        <div class="mt-4">
                            <x-input-label :value="__('Huidige afbeelding')" />
                            <div class="mt-1">
                                <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="max-h-48 rounded-lg object-contain bg-gray-100 dark:bg-gray-700">
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <x-input-label for="image" :value="__('Nieuwe afbeelding uploaden (optioneel)')" />
                        <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="block mt-1 w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p class="text-xs text-gray-500 mt-1">Max 5MB. Formats: PNG, JPEG, WebP. Vervangt de huidige afbeelding.</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.products.show', $product) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Annuleren
                        </a>
                        <x-primary-button class="ms-3">
                            {{ __('Opslaan') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
