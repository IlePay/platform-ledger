<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Paramètres système';
    protected static ?string $navigationGroup = 'System';
    protected static string $view = 'filament.pages.manage-settings';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'limit_basic_daily' => SystemSetting::getValue('limit_basic_daily', 50000),
            'limit_basic_monthly' => SystemSetting::getValue('limit_basic_monthly', 500000),
            'limit_standard_daily' => SystemSetting::getValue('limit_standard_daily', 500000),
            'limit_standard_monthly' => SystemSetting::getValue('limit_standard_monthly', 5000000),
            'limit_premium_daily' => SystemSetting::getValue('limit_premium_daily', 5000000),
            'limit_premium_monthly' => SystemSetting::getValue('limit_premium_monthly', 50000000),
            'merchant_commission' => SystemSetting::getValue('merchant_commission', 1.5),
            'transfer_fee_min' => SystemSetting::getValue('transfer_fee_min', 0),
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Limites BASIC')
                    ->schema([
                        Forms\Components\TextInput::make('limit_basic_daily')
                            ->label('Limite quotidienne (XAF)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('limit_basic_monthly')
                            ->label('Limite mensuelle (XAF)')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Limites STANDARD')
                    ->schema([
                        Forms\Components\TextInput::make('limit_standard_daily')
                            ->label('Limite quotidienne (XAF)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('limit_standard_monthly')
                            ->label('Limite mensuelle (XAF)')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Limites PREMIUM')
                    ->schema([
                        Forms\Components\TextInput::make('limit_premium_daily')
                            ->label('Limite quotidienne (XAF)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('limit_premium_monthly')
                            ->label('Limite mensuelle (XAF)')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Frais & Commissions')
                    ->schema([
                        Forms\Components\TextInput::make('merchant_commission')
                            ->label('Commission marchands (%)')
                            ->numeric()
                            ->step(0.1)
                            ->required()
                            ->helperText('Commission prélevée sur les paiements marchands'),
                        Forms\Components\TextInput::make('transfer_fee_min')
                            ->label('Frais minimum transfert P2P (XAF)')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            SystemSetting::setValue($key, $value, 'number', $this->getCategoryFromKey($key));
        }
        
        Notification::make()
            ->success()
            ->title('Paramètres enregistrés')
            ->send();
    }
    
    private function getCategoryFromKey($key): string
    {
        if (str_starts_with($key, 'limit_')) return 'LIMITS';
        if (in_array($key, ['merchant_commission', 'transfer_fee_min'])) return 'FEES';
        return 'GENERAL';
    }
}
