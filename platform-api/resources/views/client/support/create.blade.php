@extends('client.layout')
@section('title', 'Nouveau ticket')
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <a href="{{ route('support.index') }}" class="text-white hover:text-secondary mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
            <h1 class="text-3xl font-bold">Nouveau ticket support</h1>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
            
            @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('support.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Catégorie *</label>
                    <select name="category" required class="w-full px-4 py-3 border rounded-lg">
                        <option value="">-- Choisir --</option>
                        <option value="DISPUTE">Litige transaction</option>
                        <option value="REFUND">Demande de remboursement</option>
                        <option value="TECHNICAL">Problème technique</option>
                        <option value="ACCOUNT">Question sur mon compte</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Transaction concernée (optionnel)</label>
                    <input type="number" name="transaction_id" class="w-full px-4 py-3 border rounded-lg" placeholder="ID de la transaction">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Sujet *</label>
                    <input type="text" name="subject" required class="w-full px-4 py-3 border rounded-lg" placeholder="Résumé du problème">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Description *</label>
                    <textarea name="description" required rows="6" class="w-full px-4 py-3 border rounded-lg" placeholder="Décrivez votre problème en détail..."></textarea>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-4 rounded-lg font-bold hover:bg-primary-dark transition">
                    <i class="fas fa-paper-plane mr-2"></i>Créer le ticket
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
