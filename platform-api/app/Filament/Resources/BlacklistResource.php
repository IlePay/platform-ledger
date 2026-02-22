<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlacklistResource\Pages;
use App\Models\Blacklist;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class BlacklistResource extends Resource
{
    protected static ?string $model = Blacklist::class;
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Blacklist';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options([
                    'IP' => 'IP Address',
                    'PHONE' => 'Téléphone',
                    'EMAIL' => 'Email',
                    'USER_ID' => 'User ID',
                ])
                ->required(),
            Forms\Components\TextInput::make('value')
                ->label('Valeur')
                ->required(),
            Forms\Components\Textarea::make('reason')
                ->label('Raison')
                ->required(),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label('Expire le (optionnel)')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\TextColumn::make('value')->searchable(),
                Tables\Columns\TextColumn::make('reason')->limit(50),
                Tables\Columns\TextColumn::make('expires_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'IP' => 'IP',
                        'PHONE' => 'Phone',
                        'EMAIL' => 'Email',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlacklists::route('/'),
            'create' => Pages\CreateBlacklist::route('/create'),
            'edit' => Pages\EditBlacklist::route('/{record}/edit'),
        ];
    }
}