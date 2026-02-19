@extends('client.layout')

@section('title', 'Dashboard Marchand - ' . $user->business_name)

@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <div class="gradient-bg text-white">

        <!-- Business Header -->
        <div class="container mx-auto px-6 pb-24 pt-8">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-store text-4xl text-secondary"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">{{ $user->business_name }}</h1>
                    <p class="text-gray-200">{{ ucfirst(strtolower($user->business_type)) }} • QR: {{ $user->qr_code }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="container mx-auto px-6 -mt-16">
        
        @if(session('success'))
        <div class="max-w-7xl mx-auto mb-6">
            <div class="bg-green-50 text-green-700 p-4 rounded-xl flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                {{ session('success') }}
            </div>
        </div>
        @endif
        
        @if($errors->any())
        <div class="max-w-7xl mx-auto mb-6">
            <div class="bg-red-50 text-red-700 p-4 rounded-xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                {{ $errors->first() }}
            </div>
        </div>
        @endif
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">

            <!-- Solde -->
            <div class="bg-white rounded-2xl shadow-lg p-6 col-span-2">
                <p class="text-gray-500 text-sm mb-1">Solde disponible</p>
                <p class="text-4xl font-bold text-primary">
                    {{ $account ? number_format($account['balance'], 0, ',', ' ') : '0' }}
                    <span class="text-xl">XAF</span>
                </p>
                <p class="text-gray-400 text-sm mt-2">
                    Disponible: {{ $account ? number_format($account['available_balance'], 0, ',', ' ') : '0' }} XAF
                </p>
            </div>

            <!-- Ventes du jour -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm">Aujourd'hui</p>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-up text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    {{ number_format($todaySales, 0, ',', ' ') }}
                    <span class="text-sm">XAF</span>
                </p>
                <p class="text-gray-400 text-sm mt-1">{{ $todayCount }} transaction(s)</p>
            </div>

            <!-- Total ventes -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm">Total ventes</p>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-primary"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    {{ number_format($user->total_sales, 0, ',', ' ') }}
                    <span class="text-sm">XAF</span>
                </p>
                <p class="text-gray-400 text-sm mt-1">{{ $user->sales_count }} vente(s)</p>
            </div>
        </div>

        <!-- QR Code & Stats -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">

            <!-- QR Code -->
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                <h3 class="font-bold text-lg mb-4">Mon QR Code</h3>
                
                <div class="w-48 h-48 bg-white border-4 border-primary rounded-xl mx-auto mb-4 flex items-center justify-center p-2">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url('/pay/' . $user->qr_code)) }}&color=2D4B9E" 
                         alt="QR Code {{ $user->qr_code }}"
                         class="w-44 h-44 mx-auto">
                </div>
                
                <p class="text-gray-500 text-sm mb-4">
                    Code: <span class="font-mono font-bold text-primary">{{ $user->qr_code }}</span>
                </p>
                
                <div class="space-y-3">
                    <a href="{{ route('merchant.pay', $user->qr_code) }}" 
                       target="_blank"
                       class="block w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition text-center">
                        <i class="fas fa-external-link-alt mr-2"></i>Page de paiement
                    </a>
                    
                    <a href="{{ route('merchant.qrcode') }}"
                       class="block w-full bg-secondary text-primary py-3 rounded-xl font-semibold hover:bg-secondary-light transition text-center">
                        <i class="fas fa-download mr-2"></i>Télécharger QR Code
                    </a>
                    
                    <button onclick="copyPaymentLink()"
                           class="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition text-center">
                        <i class="fas fa-copy mr-2"></i>Copier le lien
                    </button>
                </div>
            </div>

            <!-- Stats rapides -->
            <div class="bg-white rounded-2xl shadow-lg p-6 md:col-span-2">
                <h3 class="font-bold text-lg mb-4">Statistiques</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-day text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Ventes aujourd'hui</p>
                                <p class="text-xs text-gray-500">{{ now()->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">{{ number_format($todaySales, 0, ',', ' ') }} XAF</p>
                            <p class="text-xs text-gray-500">{{ $todayCount }} transaction(s)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-week text-primary"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Cette semaine</p>
                                <p class="text-xs text-gray-500">{{ now()->startOfWeek()->format('d/m') }} - {{ now()->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-primary">{{ number_format($weeklySales ?? 0, 0, ',', ' ') }} XAF</p>
                            <p class="text-xs text-gray-500">{{ $weeklyCount ?? 0 }} transaction(s)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-secondary"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Ce mois</p>
                                <p class="text-xs text-gray-500">{{ now()->format('F Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-secondary">{{ number_format($monthlySales ?? 0, 0, ',', ' ') }} XAF</p>
                            <p class="text-xs text-gray-500">{{ $monthlyCount ?? 0 }} transaction(s)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-infinity text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Total cumulé</p>
                                <p class="text-xs text-gray-500">Depuis le début</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-purple-600">{{ number_format($user->total_sales, 0, ',', ' ') }} XAF</p>
                            <p class="text-xs text-gray-500">{{ $user->sales_count }} vente(s)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières transactions -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-lg">Dernières ventes</h3>
                <span class="text-gray-500 text-sm">{{ $sales->total() }} transaction(s) au total</span>
            </div>

            @if($sales->count() > 0)
                <div class="space-y-3">
                    @foreach($sales as $sale)
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-lg">Dernières ventes</h3>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm">{{ $sales->total() }} transaction(s) au total</span>
                            
                            <!-- Bouton Export -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition flex items-center gap-2">
                                    <i class="fas fa-download"></i>
                                    Exporter
                                </button>
                                
                                <!-- Dropdown -->
                                <div x-show="open" 
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-50 border">
                                    <div class="p-2">
                                        <p class="text-xs font-semibold text-gray-500 px-3 py-2">FORMAT</p>
                                        <a href="{{ route('merchant.export', ['format' => 'pdf', 'period' => 'all']) }}"
                                        class="block px-3 py-2 hover:bg-gray-100 rounded text-sm">
                                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>PDF - Toutes
                                        </a>
                                        <a href="{{ route('merchant.export', ['format' => 'csv', 'period' => 'all']) }}"
                                        class="block px-3 py-2 hover:bg-gray-100 rounded text-sm">
                                            <i class="fas fa-file-csv text-green-500 mr-2"></i>CSV - Toutes
                                        </a>
                                        
                                        <div class="border-t my-2"></div>
                                        <p class="text-xs font-semibold text-gray-500 px-3 py-2">PÉRIODE</p>
                                        
                                        <a href="{{ route('merchant.export', ['format' => 'pdf', 'period' => 'today']) }}"
                                        class="block px-3 py-2 hover:bg-gray-100 rounded text-sm">
                                            <i class="fas fa-calendar-day text-blue-500 mr-2"></i>Aujourd'hui (PDF)
                                        </a>
                                        <a href="{{ route('merchant.export', ['format' => 'pdf', 'period' => 'week']) }}"
                                        class="block px-3 py-2 hover:bg-gray-100 rounded text-sm">
                                            <i class="fas fa-calendar-week text-blue-500 mr-2"></i>Cette semaine (PDF)
                                        </a>
                                        <a href="{{ route('merchant.export', ['format' => 'pdf', 'period' => 'month']) }}"
                                        class="block px-3 py-2 hover:bg-gray-100 rounded text-sm">
                                            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Ce mois (PDF)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-arrow-down text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold">
                                    {{ $sale->fromUser ? $sale->fromUser->full_name : 'Client' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $sale->created_at->format('d/m/Y à H:i') }}
                                </p>
                                @if($sale->description)
                                    <p class="text-xs text-gray-400">{{ $sale->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600 text-lg">
                                +{{ number_format($sale->amount, 0, ',', ' ') }} XAF
                            </p>
                            <div class="flex items-center gap-2 mt-2 justify-end">
                                @if($sale->isRefunded())
                                    <span class="text-xs bg-gray-200 text-gray-700 px-3 py-1 rounded-full">
                                        <i class="fas fa-undo mr-1"></i>Remboursé
                                    </span>
                                @else
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                        {{ $sale->status }}
                                    </span>
                                    <button onclick="openRefundModal({{ $sale->id }}, {{ $sale->amount }})"
                                            class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full hover:bg-yellow-200 transition">
                                        <i class="fas fa-undo mr-1"></i>Rembourser
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $sales->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-receipt text-4xl mb-4"></i>
                    <p>Aucune vente pour le moment</p>
                    <p class="text-sm mt-2">Partagez votre QR code pour recevoir des paiements</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Remboursement -->
<div id="refundModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">Rembourser la transaction</h3>
        <form id="refundForm" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Montant à rembourser</label>
                <input type="number" name="amount" id="refund_amount" 
                       class="w-full px-4 py-3 border rounded-lg" required>
                <p class="text-xs text-gray-500 mt-1">Max: <span id="refund_max"></span> XAF</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Raison (optionnel)</label>
                <textarea name="reason" rows="3" 
                          class="w-full px-4 py-3 border rounded-lg"
                          placeholder="Ex: Produit défectueux, Annulation client..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeRefundModal()"
                        class="flex-1 px-4 py-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition font-semibold">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-semibold">
                    <i class="fas fa-undo mr-2"></i>Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function copyPaymentLink() {
    const link = '{{ url("/pay/" . $user->qr_code) }}';
    navigator.clipboard.writeText(link).then(() => {
        alert('✅ Lien copié : ' + link);
    });
}

function openRefundModal(transactionId, amount) {
    document.getElementById('refundModal').classList.remove('hidden');
    document.getElementById('refund_amount').value = amount;
    document.getElementById('refund_amount').max = amount;
    document.getElementById('refund_max').textContent = amount.toLocaleString();
    document.getElementById('refundForm').action = `/merchant/transactions/${transactionId}/refund`;
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
}

// Fermer modal en cliquant dehors
document.getElementById('refundModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRefundModal();
    }
});
</script>
@endpush

@endsection
