<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoneyRequestResource\Pages;
use App\Models\MoneyRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class MoneyRequestResource extends Resource
{
    protected static ?string $model = MoneyRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Money Requests';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request Details')->schema([
                Forms\Components\TextInput::make('requester.full_name')
                    ->label('Demandeur')
                    ->disabled(),
                Forms\Components\TextInput::make('payer.full_name')
                    ->label('Payeur')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('Montant (XAF)')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'PENDING' => 'En attente',
                        'ACCEPTED' => 'Accepté',
                        'DECLINED' => 'Refusé',
                        'EXPIRED' => 'Expiré',
                    ])
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Message & Dates')->schema([
                Forms\Components\Textarea::make('message')
                    ->disabled()
                    ->rows(2),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Créé le')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expire le')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('responded_at')
                    ->label('Répondu le')
                    ->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requester.full_name')
                    ->label('Demandeur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payer.full_name')
                    ->label('Payeur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('XAF')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'ACCEPTED',
                        'danger' => 'DECLINED',
                        'secondary' => 'EXPIRED',
                    ]),
                Tables\Columns\TextColumn::make('message')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expire')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'En attente',
                        'ACCEPTED' => 'Accepté',
                        'DECLINED' => 'Refusé',
                        'EXPIRED' => 'Expiré',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('expire')
                    ->label('Forcer expiration')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->action(function($record) {
                        $record->update([
                            'status' => 'EXPIRED',
                            'expires_at' => now(),
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Demande expirée')
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('stats')
                    ->label('Voir statistiques')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn() => route('filament.admin.resources.money-requests.stats'))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoneyRequests::route('/'),
            'stats' => Pages\MoneyRequestStats::route('/stats'),
            'view' => Pages\ViewMoneyRequest::route('/{record}'),
            
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'PENDING')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // Widgets pour stats
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\MoneyRequestResource\Widgets\MoneyRequestStatsWidget::class,
        ];
    }
}