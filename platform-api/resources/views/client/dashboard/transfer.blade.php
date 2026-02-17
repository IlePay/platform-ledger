@extends('client.layout')

@section('title', 'Envoyer de l\'argent - IlePay')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white py-6">
        <div class="container mx-auto px-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('client.dashboard') }}" class="text-white hover:text-secondary">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-2xl font-bold">Envoyer de l'argent</h1>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 py-8">
        <div class="max-w-md mx-auto">
            <!-- Balance -->
            <div class="bg-white rounded-xl p-6 mb-6">
                <p class="text-gray-500 text-sm">Solde disponible</p>
                <p class="text-3xl font-bold">
                    {{ $account ? number_format($account['available_balance'], 0, ',', ' ') : '0' }} XAF
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl p-6">
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('client.transfer.send') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Numéro du destinataire
                        </label>
                        <input 
                            type="tel" 
                            name="to_phone" 
                            required
                            placeholder="+237 6XX XXX XXX"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Montant
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                name="amount" 
                                required
                                min="100"
                                placeholder="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                            <span class="absolute right-4 top-3 text-gray-500">XAF</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum: 100 XAF</p>
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
                        <i class="fas fa-paper-plane mr-2"></i>Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
