<x-filament-panels::page>
    @php
        $total = \App\Models\MoneyRequest::count();
        $pending = \App\Models\MoneyRequest::where('status', 'PENDING')->count();
        $accepted = \App\Models\MoneyRequest::where('status', 'ACCEPTED')->count();
        $declined = \App\Models\MoneyRequest::where('status', 'DECLINED')->count();
        
        $totalAmount = \App\Models\MoneyRequest::where('status', 'ACCEPTED')->sum('amount');
        $avgAmount = \App\Models\MoneyRequest::where('status', 'ACCEPTED')->avg('amount') ?? 0;
        
        $acceptanceRate = $total > 0 ? round(($accepted / $total) * 100, 2) : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total demandes</p>
            <p class="text-3xl font-bold text-primary">{{ $total }}</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">En attente</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $pending }}</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Acceptées</p>
            <p class="text-3xl font-bold text-green-600">{{ $accepted }}</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Refusées</p>
            <p class="text-3xl font-bold text-red-600">{{ $declined }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Montant total accepté</p>
            <p class="text-2xl font-bold">{{ number_format($totalAmount, 0, ',', ' ') }} XAF</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Montant moyen</p>
            <p class="text-2xl font-bold">{{ number_format($avgAmount, 0, ',', ' ') }} XAF</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Taux d'acceptation</p>
            <p class="text-2xl font-bold text-green-600">{{ $acceptanceRate }}%</p>
        </div>
    </div>
</x-filament-panels::page>
