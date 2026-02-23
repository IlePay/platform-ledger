<x-filament-panels::page>
    @php
        $stats = $this->getStats();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total actifs</p>
            <p class="text-3xl font-bold text-primary">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500 mt-2">dont {{ $stats['autoPay'] }} en auto-pay</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">À traiter aujourd'hui</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['dueToday'] }}</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">7 prochains jours</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['nextWeek'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Montant total récurrent</p>
            <p class="text-2xl font-bold">{{ number_format($stats['totalAmount'], 0, ',', ' ') }} XAF</p>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Revenus mensuels estimés</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['monthlyRevenue'], 0, ',', ' ') }} XAF</p>
        </div>
    </div>
</x-filament-panels::page>
