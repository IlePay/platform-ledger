<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::where('role', 'USER')->count();
        $activeUsers = User::where('role', 'USER')->where('is_active', true)->count();
        $newUsersToday = User::where('role', 'USER')
            ->whereDate('created_at', today())
            ->count();

        return [
            Stat::make('Total Utilisateurs', $totalUsers)
                ->description('Utilisateurs inscrits')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, $totalUsers]),
            
            Stat::make('Utilisateurs Actifs', $activeUsers)
                ->description('Comptes actifs')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
            
            Stat::make('Nouveaux Aujourd\'hui', $newUsersToday)
                ->description('Inscriptions du jour')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($newUsersToday > 0 ? 'success' : 'warning'),
        ];
    }
}