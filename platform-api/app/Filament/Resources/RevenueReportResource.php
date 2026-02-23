<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenueReportResource\Pages;
use App\Models\RevenueReport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class RevenueReportResource extends Resource
{
    protected static ?string $model = RevenueReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Revenue Reports';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('period_type'),
                Tables\Columns\TextColumn::make('total_gmv')
                    ->label('GMV')
                    ->money('XAF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_commission')
                    ->label('Commission (1.5%)')
                    ->money('XAF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_count')
                    ->label('Transactions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_users_count')
                    ->label('Nouveaux users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_merchants')
                    ->label('Marchands actifs')
                    ->sortable(),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('period_type')
                    ->options([
                        'DAILY' => 'Quotidien',
                        'WEEKLY' => 'Hebdomadaire',
                        'MONTHLY' => 'Mensuel',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn($record) => route('admin.report.pdf', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_today')
                    ->label('Générer rapport aujourd\'hui')
                    ->action(function() {
                        RevenueReport::generateDaily(today());
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Rapport généré')
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenueReports::route('/'),
        ];
    }
}