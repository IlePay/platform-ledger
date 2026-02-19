@extends('client.layout')
@section('title', 'Mon Compte - IlePay')
@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Balance Card -->
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <div class="max-w-2xl mx-auto">
                <p class="text-gray-200 mb-2">Bonjour, {{ auth()->user()->first_name }} 👋</p>
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
                    <p class="text-gray-200 text-sm mb-2">Solde disponible</p>
                    <h1 class="text-5xl font-bold mb-6">
                        {{ $account ? number_format($account['balance'], 0, ',', ' ') : '0' }}
                        <span class="text-2xl">XAF</span>
                    </h1>

                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('client.transfer') }}"
                           class="bg-secondary text-primary px-6 py-3 rounded-xl font-semibold hover:bg-secondary-light transition text-center">
                            <i class="fas fa-paper-plane mr-2"></i>Envoyer
                        </a>
                        <a href="{{ route('money-request.create') }}" 
                            class="bg-white/20 text-white py-3 px-6 rounded-xl font-semibold hover:bg-white/30 transition text-center">
                            <i class="fas fa-hand-holding-usd mr-2"></i>Recevoir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-12">
        <div class="max-w-2xl mx-auto">

            @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-500 text-sm">Total envoyé</p>
                    <p class="text-xl font-bold text-red-600">{{ number_format($totalSent, 0, ',', ' ') }} XAF</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-500 text-sm">Total reçu</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($totalReceived, 0, ',', ' ') }} XAF</p>
                </div>
            </div>

            <!-- Recherche -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <form method="GET" class="grid md:grid-cols-4 gap-4">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher..." class="px-4 py-2 border rounded-lg">
                    <select name="type" class="px-4 py-2 border rounded-lg">
                        <option value="">Tous types</option>
                        <option value="TRANSFER" {{ $type == 'TRANSFER' ? 'selected' : '' }}>Transfert</option>
                        <option value="REFUND" {{ $type == 'REFUND' ? 'selected' : '' }}>Remboursement</option>
                    </select>
                    <select name="period" class="px-4 py-2 border rounded-lg">
                        <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Toute période</option>
                        <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Ce mois</option>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Rechercher
                    </button>
                </form>
                <a href="{{ route('transactions.export', ['period' => $period]) }}" class="inline-block mt-4 bg-green-500 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-download mr-2"></i>Exporter CSV
                </a>
            </div>

            <!-- Transactions -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">Transactions récentes</h2>
                    <span class="text-gray-400 text-sm">{{ $transactions->total() }} au total</span>
                </div>

                @forelse($transactions as $tx)
                @php
                    $isSent = $tx->from_user_id === auth()->id();
                    $otherUser = $isSent ? $tx->toUser : $tx->fromUser;
                @endphp
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isSent ? 'bg-red-100' : 'bg-green-100' }}">
                            <i class="fas {{ $isSent ? 'fa-arrow-up text-red-600' : 'fa-arrow-down text-green-600' }}"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ $isSent ? 'Envoyé à' : 'Reçu de' }} {{ $otherUser?->full_name ?? 'Inconnu' }}</p>
                            <p class="text-xs text-gray-500">{{ $tx->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                    <p class="font-bold text-lg {{ $isSent ? 'text-red-600' : 'text-green-600' }}">
                        {{ $isSent ? '-' : '+' }}{{ number_format($tx->amount, 0, ',', ' ') }} XAF
                    </p>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-exchange-alt text-4xl mb-4"></i>
                    <p>Aucune transaction</p>
                </div>
                @endforelse

                @if($transactions->hasPages())
                <div class="mt-6">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
