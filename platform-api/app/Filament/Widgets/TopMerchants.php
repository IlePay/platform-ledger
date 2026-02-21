<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopMerchants extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): ?Builder
    {
        return User::query()
            ->where('account_type', 'MERCHANT')
            ->orderBy('total_sales', 'desc')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('business_name')->label('Marchand')->searchable(),
            Tables\Columns\TextColumn::make('business_type')->badge(),
            Tables\Columns\TextColumn::make('total_sales')->label('Ventes totales')->money('XAF')->sortable(),
            Tables\Columns\TextColumn::make('sales_count')->label('Nb transactions')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Membre depuis')->date(),
        ];
    }

    protected function getTableHeading(): string
    {
        return 'Top 10 Marchands';
    }
}
