<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class CommissionReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Rapport Commissions';
    protected static ?string $navigationGroup = 'Analytics';
    protected static string $view = 'filament.pages.commission-report';
    
    public $period = 'month'; // today, week, month, all
    public $merchantId = null;
    
    public function getStats()
    {
        $query = Transaction::where('type', 'PAYMENT')
            ->where('status', 'COMPLETED')
            ->whereNotNull('metadata');
        
        // Filtrer par période
        switch ($this->period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
        }
        
        // Filtrer par marchand si spécifié
        if ($this->merchantId) {
            $query->where('to_user_id', $this->merchantId);
        }
        
        $transactions = $query->get();
        
        $totalCommission = 0;
        $totalGMV = 0;
        $merchantsData = [];
        
        foreach ($transactions as $tx) {
            $metadata = is_string($tx->metadata) ? json_decode($tx->metadata, true) : $tx->metadata;
            $commission = $metadata['commission'] ?? 0;
            
            $totalCommission += $commission;
            $totalGMV += $tx->amount;
            
            $merchantName = $tx->toUser->business_name ?? $tx->toUser->full_name;
            
            if (!isset($merchantsData[$merchantName])) {
                $merchantsData[$merchantName] = [
                    'name' => $merchantName,
                    'commission' => 0,
                    'gmv' => 0,
                    'count' => 0,
                ];
            }
            
            $merchantsData[$merchantName]['commission'] += $commission;
            $merchantsData[$merchantName]['gmv'] += $tx->amount;
            $merchantsData[$merchantName]['count']++;
        }
        
        // Trier par commission décroissante
        usort($merchantsData, fn($a, $b) => $b['commission'] <=> $a['commission']);
        
        return [
            'total_commission' => $totalCommission,
            'total_gmv' => $totalGMV,
            'transaction_count' => $transactions->count(),
            'avg_commission' => $transactions->count() > 0 ? $totalCommission / $transactions->count() : 0,
            'commission_rate' => $totalGMV > 0 ? ($totalCommission / $totalGMV) * 100 : 0,
            'merchants' => $merchantsData,
        ];
    }
}