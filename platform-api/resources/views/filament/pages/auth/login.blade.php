<x-filament-panels::page.simple>
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="IlePay" class="h-16 mx-auto mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Panneau d'administration</h2>
            <p class="text-gray-600 mt-2">Connectez-vous pour gérer IlePay</p>
        </div>

        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    </div>
</x-filament-panels::page.simple>
