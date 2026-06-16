<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Klant: {{ $customer->name }}
            </h2>
            <div class="space-x-3">
                <a href="{{ route('admin.customers.edit', $customer) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                    Bewerken
                </a>
                <a href="{{ route('admin.customers.password.edit', $customer) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                    Wachtwoord wijzigen
                </a>
                <a href="{{ route('admin.customers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                    ← Terug naar overzicht
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Klantgegevens</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Naam</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Geregistreerd</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->created_at->locale('nl')->isoFormat('DD-MM-YYYY') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Totaal bestellingen</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $orders->count() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Bestelgeschiedenis</h3>
                    @if($orders->isEmpty())
                        <p class="text-gray-500">Deze klant heeft nog geen bestellingen geplaatst.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Medicijn</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hoeveelheid</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $order->product_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $order->amount }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            @php
                                                $statusLabels = [
                                                    'pending' => 'In afwachting',
                                                    'ready' => 'Klaar',
                                                    'cancelled' => 'Geannuleerd',
                                                    'rejected' => 'Afgekeurd',
                                                    'completed' => 'Afgerond',
                                                ];
                                                $statusClasses = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                    'ready' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                    'rejected' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                ];
                                            @endphp
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusClasses[$order->status] ?? $statusClasses['pending'] }}">
                                                {{ $statusLabels[$order->status] ?? $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->locale('nl')->isoFormat('DD-MM-YYYY') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
