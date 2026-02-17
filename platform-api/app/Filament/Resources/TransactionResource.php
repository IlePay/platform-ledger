<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'Gestion';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Détails de la Transaction')
                ->schema([
                    Forms\Components\TextInput::make('ledger_transaction_id')
                        ->label('ID Ledger')
                        ->disabled()
                        ->dehydrated(false),
                    
                    Forms\Components\TextInput::make('idempotency_key')
                        ->label('Clé d\'Idempotence')
                        ->disabled()
                        ->dehydrated(false),
                    
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('from_user_id')
                                ->label('De (User ID)')
                                ->disabled()
                                ->dehydrated(false),
                            
                            Forms\Components\TextInput::make('to_user_id')
                                ->label('À (User ID)')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    
                    Forms\Components\TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()
                        ->suffix('XAF')
                        ->disabled()
                        ->dehydrated(false),
                    
                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options([
                            'TRANSFER' => 'Transfert',
                            'CREDIT' => 'Crédit',
                            'DEBIT' => 'Débit',
                            'FEE' => 'Frais',
                            'REFUND' => 'Remboursement',
                        ])
                        ->disabled()
                        ->dehydrated(false),
                    
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'PENDING' => 'En attente',
                            'COMPLETED' => 'Complété',
                            'FAILED' => 'Échoué',
                            'REVERSED' => 'Annulé',
                        ])
                        ->disabled()
                        ->dehydrated(false),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(1),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->searchable(),
            
            Tables\Columns\TextColumn::make('fromUser.full_name')
                ->label('De')
                ->searchable(['first_name', 'last_name', 'phone'])
                ->sortable(),
            
            Tables\Columns\TextColumn::make('toUser.full_name')
                ->label('À')
                ->searchable(['first_name', 'last_name', 'phone'])
                ->sortable(),
            
            Tables\Columns\TextColumn::make('amount')
                ->label('Montant')
                ->money('XAF')
                ->sortable()
                ->color('success')
                ->weight('bold'),
            
            Tables\Columns\BadgeColumn::make('type')
                ->label('Type')
                ->colors([
                    'primary' => 'TRANSFER',
                    'success' => 'CREDIT',
                    'danger' => 'DEBIT',
                    'warning' => 'FEE',
                    'info' => 'REFUND',
                ]),
            
            Tables\Columns\BadgeColumn::make('status')
                ->label('Statut')
                ->colors([
                    'warning' => 'PENDING',
                    'success' => 'COMPLETED',
                    'danger' => 'FAILED',
                    'gray' => 'REVERSED',
                ]),
            
            Tables\Columns\TextColumn::make('created_at')
                ->label('Date')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('type')
                ->label('Type')
                ->options([
                    'TRANSFER' => 'Transfert',
                    'CREDIT' => 'Crédit',
                    'DEBIT' => 'Débit',
                    'FEE' => 'Frais',
                    'REFUND' => 'Remboursement',
                ]),
            
            Tables\Filters\SelectFilter::make('status')
                ->label('Statut')
                ->options([
                    'PENDING' => 'En attente',
                    'COMPLETED' => 'Complété',
                    'FAILED' => 'Échoué',
                    'REVERSED' => 'Annulé',
                ]),
            
            Tables\Filters\Filter::make('created_at')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from')
                        ->label('Du'),
                    \Filament\Forms\Components\DatePicker::make('until')
                        ->label('Au'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                }),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\ExportBulkAction::make()
                    ->label('Exporter'),
            ]),
        ])
        ->defaultSort('created_at', 'desc');
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
