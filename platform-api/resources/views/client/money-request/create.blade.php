@extends('client.layout')
@section('title', 'Demander de l\'argent')
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <a href="{{ route('client.dashboard') }}" class="text-white hover:text-secondary mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
            <h1 class="text-3xl font-bold">Demander de l'argent</h1>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
            @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6">
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('money-request.store') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Numéro du payeur</label>
                    <input type="tel" name="payer_phone" required
                           class="w-full px-4 py-3 border rounded-lg"
                           placeholder="+237670000000">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Montant (XAF)</label>
                    <input type="number" name="amount" required min="100"
                           class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Message (optionnel)</label>
                    <textarea name="message" rows="3" 
                              class="w-full px-4 py-3 border rounded-lg"
                              placeholder="Ex: Remboursement repas"></textarea>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-4 rounded-lg font-bold hover:bg-primary-dark transition">
                    <i class="fas fa-paper-plane mr-2"></i>Envoyer la demande
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
