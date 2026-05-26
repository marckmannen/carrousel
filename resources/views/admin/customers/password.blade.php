<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Wachtwoord wijzigen') }}
            </h2>
            <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                ← Terug
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Een nieuw wachtwoord instellen voor <strong>{{ $customer->name }}</strong> ({{ $customer->email }}).
                </p>

                <form method="POST" action="{{ route('admin.customers.password.update', $customer) }}">
                    @csrf
                    @method('PATCH')

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Nieuw wachtwoord')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Bevestig wachtwoord')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Annuleren
                        </a>
                        <x-primary-button class="ms-3">
                            {{ __('Wachtwoord wijzigen') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
