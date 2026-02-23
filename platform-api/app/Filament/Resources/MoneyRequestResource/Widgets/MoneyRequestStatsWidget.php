<?php

namespace App\Filament\Resources\MoneyRequestResource\Widgets;

use App\Models\MoneyRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MoneyRequestStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = MoneyRequest::count();
        $pending = MoneyRequest::where('status', 'PENDING')->count();
        $accepted = MoneyRequest::where('status', 'ACCEPTED')->count();
        $declined = MoneyRequest::where('status', 'DECLINED')->count();
        
        $acceptanceRate = $total > 0 ? round(($accepted / $total) * 100, 1) : 0;
        
        return [
            Stat::make('Total demandes', $total)
                ->description('Toutes les demandes')
                ->color('primary'),
            Stat::make('En attente', $pending)
                ->description('Demandes en attente')
                ->color('warning'),
            Stat::make('Acceptées', $accepted)
                ->description("Taux: {$acceptanceRate}%")
                ->color('success'),
            Stat::make('Refusées', $declined)
                ->description('Demandes refusées')
                ->color('danger'),
        ];
    }
}
