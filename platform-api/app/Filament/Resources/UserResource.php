<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Utilisateurs';
    protected static ?string $navigationBadgeColor = 'primary';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('role', 'USER')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('KYC & Limites')
                    ->schema([
                        Forms\Components\Select::make('kyc_level')
                            ->label('Niveau KYC')
                            ->options([
                                'NONE' => 'Aucun',
                                'BASIC' => 'Basique',
                                'STANDARD' => 'Standard',
                                'PREMIUM' => 'Premium',
                            ])
                            ->required()
                            ->default('NONE'),
                        Forms\Components\TextInput::make('daily_limit')
                            ->label('Limite Quotidienne')
                            ->numeric()
                            ->suffix('XAF')
                            ->default(50000),
                        Forms\Components\TextInput::make('monthly_limit')
                            ->label('Limite Mensuelle')
                            ->numeric()
                            ->suffix('XAF')
                            ->default(500000),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Compte actif')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom Complet')
                    ->searchable(['first_name', 'last_name', 'name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'MERCHANT' => 'warning',
                        default => 'primary',
                    }),
                Tables\Columns\BadgeColumn::make('kyc_level')
                    ->label('KYC')
                    ->colors([
                        'danger' => 'NONE',
                        'warning' => 'BASIC',
                        'primary' => 'STANDARD',
                        'success' => 'PREMIUM',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('daily_limit')
                    ->label('Limite/Jour')
                    ->money('XAF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kyc_level')
                    ->label('Niveau KYC')
                    ->options([
                        'NONE' => 'Aucun',
                        'BASIC' => 'Basique',
                        'STANDARD' => 'Standard',
                        'PREMIUM' => 'Premium',
                    ]),
                Tables\Filters\SelectFilter::make('account_type')
                    ->label('Type de compte')
                    ->options([
                        'PERSONAL' => 'Personnel',
                        'MERCHANT' => 'Marchand',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->trueLabel('Actifs seulement')
                    ->falseLabel('Bloqués seulement'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    // ✅ ACTION CRÉDIT - Appelle vraiment le Ledger
                    Tables\Actions\Action::make('credit')
                        ->label('Créditer')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Montant')
                                ->numeric()
                                ->required()
                                ->suffix('XAF')
                                ->minValue(100)
                                ->maxValue(10000000),
                            Forms\Components\Textarea::make('reason')
                                ->label('Raison')
                                ->required()
                                ->default('Crédit administrateur')
                                ->maxLength(255),
                        ])
                        ->action(function (User $record, array $data) {
                            try {
                                $ledger = app(\App\Services\LedgerClient::class);

                                $systemAccountId = config('ledger.system_account_id');

                                if (!$systemAccountId) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Erreur Configuration')
                                        ->danger()
                                        ->body('LEDGER_SYSTEM_ACCOUNT_ID non configuré dans .env')
                                        ->send();
                                    return;
                                }

                                if (!$record->ledger_account_id) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Erreur')
                                        ->danger()
                                        ->body('Cet utilisateur n\'a pas de compte Ledger')
                                        ->send();
                                    return;
                                }

                                $transfer = $ledger->createTransfer(
                                    \Str::uuid()->toString(),
                                    $systemAccountId,
                                    $record->ledger_account_id,
                                    (float) $data['amount'],
                                    'XAF',
                                    $data['reason']
                                );

                                if (!$transfer) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Échec du crédit')
                                        ->danger()
                                        ->body('Vérifiez que le Go Ledger est démarré sur le port 8082')
                                        ->send();
                                    return;
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('✅ Compte crédité !')
                                    ->success()
                                    ->body("{$record->full_name} a été crédité de " . number_format($data['amount'], 0, ',', ' ') . " XAF")
                                    ->send();

                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erreur')
                                    ->danger()
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),

                    // ✅ ACTION DÉBIT - Appelle vraiment le Ledger
                    Tables\Actions\Action::make('debit')
                        ->label('Débiter')
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Montant')
                                ->numeric()
                                ->required()
                                ->suffix('XAF')
                                ->minValue(100),
                            Forms\Components\Textarea::make('reason')
                                ->label('Raison')
                                ->required()
                                ->default('Débit administrateur')
                                ->maxLength(255),
                        ])
                        ->action(function (User $record, array $data) {
                            try {
                                $ledger = app(\App\Services\LedgerClient::class);

                                $systemAccountId = config('ledger.system_account_id');

                                if (!$systemAccountId || !$record->ledger_account_id) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Erreur Configuration')
                                        ->danger()
                                        ->body('Configuration Ledger manquante')
                                        ->send();
                                    return;
                                }

                                $transfer = $ledger->createTransfer(
                                    \Str::uuid()->toString(),
                                    $record->ledger_account_id,
                                    $systemAccountId,
                                    (float) $data['amount'],
                                    'XAF',
                                    $data['reason']
                                );

                                if (!$transfer) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Échec du débit')
                                        ->danger()
                                        ->body('Solde insuffisant ou Ledger non démarré')
                                        ->send();
                                    return;
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('✅ Compte débité !')
                                    ->warning()
                                    ->body("{$record->full_name} a été débité de " . number_format($data['amount'], 0, ',', ' ') . " XAF")
                                    ->send();

                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erreur')
                                    ->danger()
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),

                    // ✅ ACTION BLOQUER/DÉBLOQUER
                    Tables\Actions\Action::make('block')
                        ->label(fn (User $record) => $record->is_active ? 'Bloquer' : 'Débloquer')
                        ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                        ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn (User $record) => $record->is_active ? 'Bloquer l\'utilisateur ?' : 'Débloquer l\'utilisateur ?')
                        ->modalDescription(fn (User $record) => $record->is_active
                            ? 'L\'utilisateur ne pourra plus effectuer de transactions.'
                            : 'L\'utilisateur pourra à nouveau effectuer des transactions.')
                        ->action(function (User $record) {
                            $record->is_active = !$record->is_active;
                            $record->save();

                            \Filament\Notifications\Notification::make()
                                ->title($record->is_active ? '✅ Utilisateur débloqué' : '🔒 Utilisateur bloqué')
                                ->success()
                                ->send();
                        }),

                    // ✅ ACTION UPGRADE KYC
                    Tables\Actions\Action::make('upgrade_kyc')
                        ->label('Upgrade KYC')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('kyc_level')
                                ->label('Nouveau Niveau KYC')
                                ->options([
                                    'BASIC' => 'Basique',
                                    'STANDARD' => 'Standard',
                                    'PREMIUM' => 'Premium',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('daily_limit')
                                ->label('Nouvelle Limite Quotidienne')
                                ->numeric()
                                ->suffix('XAF')
                                ->default(fn (User $record) => $record->daily_limit),
                            Forms\Components\TextInput::make('monthly_limit')
                                ->label('Nouvelle Limite Mensuelle')
                                ->numeric()
                                ->suffix('XAF')
                                ->default(fn (User $record) => $record->monthly_limit),
                        ])
                        ->action(function (User $record, array $data) {
                            $record->update([
                                'kyc_level' => $data['kyc_level'],
                                'daily_limit' => $data['daily_limit'],
                                'monthly_limit' => $data['monthly_limit'],
                                'kyc_verified_at' => now(),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('✅ KYC mis à jour')
                                ->success()
                                ->body("Le niveau KYC de {$record->full_name} est maintenant {$data['kyc_level']}")
                                ->send();
                        }),

                    // ✅ ACTION VOIR SOLDE
                    Tables\Actions\Action::make('view_balance')
                        ->label('Voir Solde')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->modalHeading(fn (User $record) => "Solde de {$record->full_name}")
                        ->modalContent(function (User $record) {
                            $ledger = app(\App\Services\LedgerClient::class);
                            $account = $record->ledger_account_id
                                ? $ledger->getAccount($record->ledger_account_id)
                                : null;

                            if (!$account) {
                                return view('filament.modals.no-account');
                            }

                            return view('filament.modals.account-balance', [
                                'account' => $account,
                                'user' => $record,
                            ]);
                        })
                        ->modalSubmitAction(false),

                ])->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'USER');
    }
}