<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\LedgerClient;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('credit')
                ->label('Créditer')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()
                        ->required()
                        ->suffix('XAF')
                        ->minValue(100),
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Raison')
                        ->required(),
                ])
                ->action(function (array $data) {
                    \Filament\Notifications\Notification::make()
                        ->title('Compte crédité')
                        ->success()
                        ->body("{$data['amount']} XAF crédités")
                        ->send();
                }),
            
            Actions\Action::make('block')
                ->label(fn () => $this->record->is_active ? 'Bloquer' : 'Débloquer')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->is_active = !$this->record->is_active;
                    $this->record->save();
                    
                    \Filament\Notifications\Notification::make()
                        ->title($this->record->is_active ? 'Débloqué' : 'Bloqué')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $ledger = app(LedgerClient::class);
        $account = $this->record->ledger_account_id 
            ? $ledger->getAccount($this->record->ledger_account_id) 
            : null;

        return $infolist
            ->schema([
                Components\Section::make('Solde du Compte')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('balance')
                                    ->label('Solde Total')
                                    ->state($account ? number_format($account['balance'], 0, ',', ' ') . ' XAF' : 'N/A')
                                    ->color('success')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->icon('heroicon-o-currency-dollar'),
                                
                                Components\TextEntry::make('available_balance')
                                    ->label('Solde Disponible')
                                    ->state($account ? number_format($account['available_balance'], 0, ',', ' ') . ' XAF' : 'N/A')
                                    ->color('primary')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->icon('heroicon-o-banknotes'),
                                
                                Components\TextEntry::make('currency')
                                    ->label('Devise')
                                    ->state($account['currency'] ?? 'N/A')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ])
                    ->visible(fn () => $account !== null)
                    ->collapsible(),

                Components\Section::make('Informations Personnelles')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('first_name')
                                    ->label('Prénom'),
                                Components\TextEntry::make('last_name')
                                    ->label('Nom'),
                                Components\TextEntry::make('phone')
                                    ->label('Téléphone')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                                Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),
                            ]),
                    ])
                    ->collapsible(),

                Components\Section::make('KYC & Limites')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('kyc_level')
                                    ->label('Niveau KYC')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'NONE' => 'gray',
                                        'BASIC' => 'warning',
                                        'STANDARD' => 'success',
                                        'PREMIUM' => 'primary',
                                    }),
                                
                                Components\TextEntry::make('daily_limit')
                                    ->label('Limite Quotidienne')
                                    ->money('XAF')
                                    ->color('warning'),
                                
                                Components\TextEntry::make('monthly_limit')
                                    ->label('Limite Mensuelle')
                                    ->money('XAF')
                                    ->color('warning'),
                            ]),
                        
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('is_active')
                                    ->label('Compte Actif')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Actif' : 'Bloqué'),
                                
                                Components\TextEntry::make('kyc_verified_at')
                                    ->label('KYC Vérifié le')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Non vérifié'),
                            ]),
                    ])
                    ->collapsible(),

                Components\Section::make('Compte Ledger')
                    ->schema([
                        Components\TextEntry::make('ledger_account_id')
                            ->label('ID Compte Ledger')
                            ->copyable()
                            ->icon('heroicon-o-key'),
                        
                        Components\TextEntry::make('ledger_status')
                            ->label('Statut Ledger')
                            ->state($account['status'] ?? 'N/A')
                            ->badge()
                            ->color('success'),
                    ])
                    ->visible(fn () => $this->record->ledger_account_id)
                    ->collapsible(),

                Components\Section::make('Informations Système')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime('d/m/Y H:i'),
                                
                                Components\TextEntry::make('updated_at')
                                    ->label('Mis à jour le')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}