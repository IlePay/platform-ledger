<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filtres -->
        <div class="flex gap-4">
            <select wire:model.live="period" class="rounded-lg border-gray-300">
                <option value="today">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="all">Tout</option>
            </select>
        </div>

        @php
            $stats = $this->getStats();
        @endphp

        <!-- Stats globales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">Commission totale</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($stats['total_commission'], 0, ',', ' ') }} XAF</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">GMV Total</p>
                <p class="text-2xl font-bold">{{ number_format($stats['total_gmv'], 0, ',', ' ') }} XAF</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">Transactions</p>
                <p class="text-2xl font-bold">{{ $stats['transaction_count'] }}</p>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">Taux moyen</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['commission_rate'], 2) }}%</p>
            </div>
        </div>

        <!-- Table par marchand -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marchand</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">GMV</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Commission</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Transactions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($stats['merchants'] as $merchant)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $merchant['name'] }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($merchant['gmv'], 0, ',', ' ') }} XAF</td>
                        <td class="px-6 py-4 text-right font-bold text-green-600">{{ number_format($merchant['commission'], 0, ',', ' ') }} XAF</td>
                        <td class="px-6 py-4 text-right">{{ $merchant['count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
