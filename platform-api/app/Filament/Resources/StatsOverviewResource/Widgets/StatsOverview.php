<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Total users
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        
        // GMV (Gross Merchandise Value)
        $gmvTotal = Transaction::where('status', 'COMPLETED')->sum('amount');
        $gmvToday = Transaction::where('status', 'COMPLETED')
            ->whereDate('created_at', today())
            ->sum('amount');
        
        // Transactions
        $txTotal = Transaction::where('status', 'COMPLETED')->count();
        $txToday = Transaction::where('status', 'COMPLETED')
            ->whereDate('created_at', today())
            ->count();
        
        // ARPU (Average Revenue Per User)
        $arpu = $totalUsers > 0 ? $gmvTotal / $totalUsers : 0;
        
        return [
            Stat::make('Utilisateurs actifs', number_format($totalUsers, 0, ',', ' '))
                ->description("+{$newUsersToday} aujourd'hui")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('GMV Total', number_format($gmvTotal, 0, ',', ' ') . ' XAF')
                ->description(number_format($gmvToday, 0, ',', ' ') . ' XAF aujourd\'hui')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
                
            Stat::make('Transactions', number_format($txTotal, 0, ',', ' '))
                ->description("{$txToday} aujourd'hui")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
                
            Stat::make('ARPU', number_format($arpu, 0, ',', ' ') . ' XAF')
                ->description('Revenue par utilisateur')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}