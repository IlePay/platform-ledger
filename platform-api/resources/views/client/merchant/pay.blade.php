@extends('client.layout')

@section('title', 'Payer ' . $merchant->business_name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-store text-4xl text-primary"></i>
            </div>
            <h1 class="text-3xl font-bold mb-2">{{ $merchant->business_name }}</h1>
            <p class="text-gray-200">{{ ucfirst(strtolower($merchant->business_type)) }}</p>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-8">
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-xl font-bold mb-6 text-center">Effectuer un paiement</h2>

                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                        {{ $errors->first() }}
                    </div>
                @endif

                @auth
                <form action="{{ route('merchant.pay.process', $merchant->qr_code) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Montant à payer
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                name="amount" 
                                required
                                min="100"
                                autofocus
                                placeholder="0"
                                class="w-full px-4 py-4 text-2xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                            <span class="absolute right-4 top-5 text-gray-500 text-xl">XAF</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Votre Code PIN
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
                        class="w-full bg-primary text-white py-4 rounded-lg font-semibold text-lg hover:bg-primary-dark transition"
                    >
                        <i class="fas fa-credit-card mr-2"></i>Payer
                    </button>
                </form>
                @else
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-6">Connectez-vous pour effectuer un paiement</p>
                    <a href="{{ route('client.login') }}" class="inline-block bg-primary text-white px-8 py-3 rounded-lg font-semibold hover:bg-primary-dark transition">
                        Se connecter
                    </a>
                </div>
                @endauth
            </div>

            <div class="mt-6 text-center">
                <a href="/" class="text-gray-600 hover:text-primary transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
