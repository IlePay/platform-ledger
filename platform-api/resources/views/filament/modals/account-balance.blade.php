<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
            <div class="text-sm text-green-600 dark:text-green-400">Solde Total</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                {{ number_format($account['balance'], 0, ',', ' ') }} XAF
            </div>
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <div class="text-sm text-blue-600 dark:text-blue-400">Solde Disponible</div>
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                {{ number_format($account['available_balance'], 0, ',', ' ') }} XAF
            </div>
        </div>
    </div>
    
    <div class="border-t pt-4">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-600 dark:text-gray-400">ID Compte Ledger</dt>
                <dd class="font-mono text-xs">{{ $account['id'] }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600 dark:text-gray-400">Devise</dt>
                <dd class="font-medium">{{ $account['currency'] }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600 dark:text-gray-400">Statut</dt>
                <dd>
                    <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded text-xs">
                        {{ $account['status'] }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>
</div>
