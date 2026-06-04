<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Andere Apotheken') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(empty($pharmacies))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        Geen andere apotheken beschikbaar op dit moment.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pharmacies as $item)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('external.show', $item['pharmacy']['id']) }}" class="hover:text-indigo-600">
                                        {{ $item['pharmacy']['name'] }}
                                    </a>
                                </h3>
                                @if(isset($item['pharmacy']['pharmacyCode']))
                                    <p class="mt-1 text-sm text-gray-500">Code: {{ $item['pharmacy']['pharmacyCode'] }}</p>
                                @endif
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ count($item['products'] ?? []) }} producten
                                    </span>
                                    <a href="{{ route('external.show', $item['pharmacy']['id']) }}"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Bekijk →
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
