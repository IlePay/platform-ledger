@extends('client.layout')

@section('title', 'Inscription - IlePay')

@section('content')
<div class="min-h-screen gradient-bg flex items-center justify-center px-6 py-12">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/images/ILEPAYHD.png" alt="IlePay" class="h-16 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-white mb-2">Rejoignez IlePay</h1>
            <p class="text-gray-200">Créez votre compte en 2 minutes</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if($errors->any())
                <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('client.register.submit') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Prénom
                        </label>
                        <input 
                            type="text" 
                            name="first_name" 
                            required
                            value="{{ old('first_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nom
                        </label>
                        <input 
                            type="text" 
                            name="last_name" 
                            required
                            value="{{ old('last_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro de téléphone
                    </label>
                    <input 
                        type="tel" 
                        name="phone" 
                        required
                        placeholder="+237 6XX XXX XXX"
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-1">Format: +237XXXXXXXXX</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Code PIN (4-6 chiffres)
                    </label>
                    <input 
                        type="password" 
                        name="pin" 
                        required
                        placeholder="••••"
                        minlength="4"
                        maxlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmer le PIN
                    </label>
                    <input 
                        type="password" 
                        name="pin_confirmation" 
                        required
                        placeholder="••••"
                        minlength="4"
                        maxlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-secondary text-primary py-3 rounded-lg font-semibold hover:bg-secondary-light transition"
                >
                    Créer mon compte
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Déjà un compte ?
                    <a href="{{ route('client.login') }}" class="text-primary font-semibold hover:underline">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="/" class="text-white hover:text-secondary transition">
                ← Retour à l'accueil
            </a>
        </div>
    </div>
</div>
@endsection
