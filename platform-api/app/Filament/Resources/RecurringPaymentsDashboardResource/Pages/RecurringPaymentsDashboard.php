<?php

namespace App\Filament\Pages;

use App\Models\RecurringPayment;
use Filament\Pages\Page;

class RecurringPaymentsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Récurrents Dashboard';
    protected static ?string $navigationGroup = 'Analytics';
    protected static string $view = 'filament.pages.recurring-payments-dashboard';
    
    public function getStats()
    {
        $total = RecurringPayment::where('is_active', true)->count();
        $autoPay = RecurringPayment::where('is_active', true)->where('auto_pay', true)->count();
        $dueToday = RecurringPayment::where('is_active', true)->where('next_payment_date', '<=', today())->count();
        $nextWeek = RecurringPayment::where('is_active', true)
            ->whereBetween('next_payment_date', [today(), today()->addWeek()])
            ->count();
        
        $totalAmount = RecurringPayment::where('is_active', true)->sum('amount');
        $monthlyRevenue = RecurringPayment::where('is_active', true)
            ->where('frequency', 'MONTHLY')
            ->sum('amount');
        
        return compact('total', 'autoPay', 'dueToday', 'nextWeek', 'totalAmount', 'monthlyRevenue');
    }
}