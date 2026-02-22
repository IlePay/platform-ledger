<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FraudAlertResource\Pages;
use App\Models\FraudAlert;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class FraudAlertResource extends Resource
{
    protected static ?string $model = FraudAlert::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationLabel = 'Fraud Alerts';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Alert Details')->schema([
                Forms\Components\TextInput::make('user.full_name')->label('User')->disabled(),
                Forms\Components\TextInput::make('transaction_id')->disabled(),
                Forms\Components\Select::make('alert_type')
                    ->options([
                        'VELOCITY' => 'Velocity (trop rapide)',
                        'HIGH_AMOUNT' => 'Montant élevé',
                        'SUSPICIOUS_PATTERN' => 'Pattern suspect',
                        'BLACKLIST' => 'Blacklist',
                    ])
                    ->disabled(),
                Forms\Components\Select::make('severity')
                    ->options([
                        'LOW' => 'Low',
                        'MEDIUM' => 'Medium',
                        'HIGH' => 'High',
                        'CRITICAL' => 'Critical',
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Review')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'PENDING' => 'En attente',
                        'REVIEWED' => 'Examiné',
                        'APPROVED' => 'Approuvé (faux positif)',
                        'BLOCKED' => 'Bloqué',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('review_notes')
                    ->label('Notes de révision')
                    ->rows(3),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('alert_type')
                    ->colors([
                        'warning' => 'VELOCITY',
                        'danger' => 'HIGH_AMOUNT',
                        'primary' => 'SUSPICIOUS_PATTERN',
                        'danger' => 'BLACKLIST',
                    ]),
                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'LOW',
                        'warning' => 'MEDIUM',
                        'danger' => 'HIGH',
                        'danger' => 'CRITICAL',
                    ]),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'info' => 'REVIEWED',
                        'success' => 'APPROVED',
                        'danger' => 'BLOCKED',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'En attente',
                        'REVIEWED' => 'Examiné',
                        'APPROVED' => 'Approuvé',
                        'BLOCKED' => 'Bloqué',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'CRITICAL' => 'Critical',
                        'HIGH' => 'High',
                        'MEDIUM' => 'Medium',
                        'LOW' => 'Low',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('block_user')
                    ->label('Bloquer user')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->user && !$record->user->is_blocked)
                    ->action(function($record) {
                        $record->user->update([
                            'is_blocked' => true,
                            'block_reason' => "Fraud alert #{$record->id}: {$record->description}",
                            'blocked_at' => now(),
                        ]);
                        $record->update(['status' => 'BLOCKED', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
                        Notification::make()->danger()->title('User bloqué')->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->action(function($record) {
                        $record->update(['status' => 'APPROVED', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
                        Notification::make()->success()->title('Alerte approuvée (faux positif)')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFraudAlerts::route('/'),
            'edit' => Pages\EditFraudAlert::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'PENDING')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}