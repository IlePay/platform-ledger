<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecurringPaymentResource\Pages;
use App\Models\RecurringPayment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class RecurringPaymentResource extends Resource
{
    protected static ?string $model = RecurringPayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Paiements Récurrents';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Parties')->schema([
                Forms\Components\Select::make('payer_id')
                    ->label('Payeur')
                    ->relationship('payer', 'full_name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('merchant_id')
                    ->label('Marchand')
                    ->relationship('merchant', 'business_name')
                    ->searchable()
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Paiement')->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Montant (XAF)')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('frequency')
                    ->label('Fréquence')
                    ->options([
                        'MONTHLY' => 'Mensuel',
                        'QUARTERLY' => 'Trimestriel',
                        'YEARLY' => 'Annuel',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('next_payment_date')
                    ->label('Prochaine échéance')
                    ->required(),
                Forms\Components\Toggle::make('auto_pay')
                    ->label('Paiement automatique')
                    ->helperText('Prélever automatiquement à la date'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Détails')->schema([
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metadata')
                    ->label('Métadonnées')
                    ->keyLabel('Clé')
                    ->valueLabel('Valeur')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payer.full_name')
                    ->label('Payeur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('merchant.business_name')
                    ->label('Marchand')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('XAF')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('frequency')
                    ->colors([
                        'primary' => 'MONTHLY',
                        'warning' => 'QUARTERLY',
                        'success' => 'YEARLY',
                    ]),
                Tables\Columns\TextColumn::make('next_payment_date')
                    ->label('Prochaine échéance')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_pay')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->defaultSort('next_payment_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('frequency'),
                Tables\Filters\TernaryFilter::make('auto_pay'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecurringPayments::route('/'),
            'create' => Pages\CreateRecurringPayment::route('/create'),
            'edit' => Pages\EditRecurringPayment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $dueToday = static::getModel()::where('is_active', true)
            ->where('next_payment_date', '<=', today())
            ->count();
        
        return $dueToday > 0 ? (string) $dueToday : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
