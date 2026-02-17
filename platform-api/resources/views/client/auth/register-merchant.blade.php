@extends('client.layout')

@section('title', 'Inscription Marchand - IlePay')

@section('content')
<div class="min-h-screen gradient-bg flex items-center justify-center px-6 py-12">
    <div class="max-w-2xl w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="IlePay" class="h-16 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-white mb-2">Compte Marchand</h1>
            <p class="text-gray-200">Acceptez des paiements pour votre business</p>
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

            <form action="{{ route('client.register.merchant.submit') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="bg-blue-50 p-4 rounded-lg mb-6">
                    <h3 class="font-semibold text-primary mb-2">Avantages Marchand :</h3>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>✓ Limite quotidienne : 500,000 XAF</li>
                        <li>✓ Limite mensuelle : 5,000,000 XAF</li>
                        <li>✓ QR Code permanent (bientôt)</li>
                        <li>✓ Dashboard ventes (bientôt)</li>
                    </ul>
                </div>

                <h3 class="font-semibold text-lg">Informations personnelles</h3>
                
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
                </div>

                <hr class="my-6">

                <h3 class="font-semibold text-lg">Informations Business</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du commerce
                    </label>
                    <input 
                        type="text" 
                        name="business_name" 
                        required
                        placeholder="Ex: Restaurant Chez Marie"
                        value="{{ old('business_name') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type de business
                    </label>
                    <select 
                        name="business_type" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="">Sélectionnez...</option>
                        <option value="RESTAURANT">Restaurant</option>
                        <option value="SHOP">Boutique / Magasin</option>
                        <option value="SERVICE">Service (Coiffure, Taxi, etc.)</option>
                        <option value="MARKET">Marché / Supermarché</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <hr class="my-6">

                <h3 class="font-semibold text-lg">Sécurité</h3>

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
                    class="w-full bg-secondary text-primary py-4 rounded-lg font-semibold hover:bg-secondary-light transition text-lg"
                >
                    Créer mon compte marchand
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Compte personnel ?
                    <a href="{{ route('client.register') }}" class="text-primary font-semibold hover:underline">
                        Inscription standard
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
