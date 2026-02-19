<x-filament-panels::page.simple>
    <div class="w-full max-w-md mx-auto">
        <!-- Logo IlePay -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="IlePay" class="h-16 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-primary">IlePay Admin</h1>
            <p class="text-gray-500 mt-2">Panneau d'administration</p>
        </div>

        <!-- Formulaire -->
        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button
                    type="submit"
                    form="authenticate"
                    class="w-full"
                    color="primary"
                    size="xl"
                >
                    <span class="text-lg">Se connecter</span>
                </x-filament::button>
            </div>
        </x-filament-panels::form>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>© {{ date('Y') }} IlePay. Tous droits réservés.</p>
            <a href="/" class="text-primary hover:underline mt-2 inline-block">
                ← Retour au site
            </a>
        </div>
    </div>

    <style>
        .fi-simple-page {
            background: linear-gradient(135deg, #2D4B9E 0%, #1e3270 100%);
        }
        .fi-simple-main {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
    </style>
</x-filament-panels::page.simple>
