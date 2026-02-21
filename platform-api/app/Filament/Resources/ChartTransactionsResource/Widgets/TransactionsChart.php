<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionsChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions (30 derniers jours)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Transaction::where('status', 'COMPLETED')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de transactions',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#2D4B9E',
                    'backgroundColor' => 'rgba(45, 75, 158, 0.1)',
                ],
                [
                    'label' => 'Volume (XAF)',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#F9B233',
                    'backgroundColor' => 'rgba(249, 178, 51, 0.1)',
                ],
            ],
            'labels' => $data->map(fn($item) => Carbon::parse($item->date)->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}