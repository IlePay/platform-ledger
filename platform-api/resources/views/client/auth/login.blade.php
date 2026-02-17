@extends('client.layout')

@section('title', 'Connexion - IlePay')

@section('content')
<div class="min-h-screen gradient-bg flex items-center justify-center px-6 py-12">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/images/ILEPAYHD.png" alt="IlePay" class="h-16 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-white mb-2">Bon retour !</h1>
            <p class="text-gray-200">Connectez-vous à votre compte</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if($errors->any())
                <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('client.login.submit') }}" method="POST" class="space-y-6">
                @csrf
                
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
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Code PIN
                    </label>
                    <input 
                        type="password" 
                        name="pin" 
                        required
                        placeholder="••••"
                        maxlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-primary-dark transition"
                >
                    Se connecter
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Pas encore de compte ?
                    <a href="{{ route('client.register') }}" class="text-primary font-semibold hover:underline">
                        Créer un compte
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
