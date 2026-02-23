<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->unique(ignoreRecord: true)
                ->disabled(fn($record) => $record !== null),
            Forms\Components\Select::make('category')
                ->options([
                    'LIMITS' => 'Limites',
                    'FEES' => 'Frais',
                    'FEATURES' => 'Features',
                    'EMAIL' => 'Email',
                    'GENERAL' => 'Général',
                ])
                ->required(),
            Forms\Components\Select::make('type')
                ->options([
                    'string' => 'Texte',
                    'number' => 'Nombre',
                    'boolean' => 'Booléen',
                    'json' => 'JSON',
                ])
                ->required()
                ->reactive(),
            Forms\Components\TextInput::make('value')
                ->label('Valeur')
                ->required()
                ->visible(fn($get) => in_array($get('type'), ['string', 'number'])),
            Forms\Components\Toggle::make('value')
                ->label('Activé')
                ->visible(fn($get) => $get('type') === 'boolean'),
            Forms\Components\Textarea::make('value')
                ->label('JSON')
                ->rows(4)
                ->visible(fn($get) => $get('type') === 'json'),
            Forms\Components\TextInput::make('description')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'LIMITS',
                        'success' => 'FEES',
                        'warning' => 'FEATURES',
                        'info' => 'EMAIL',
                    ]),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'LIMITS' => 'Limites',
                        'FEES' => 'Frais',
                        'FEATURES' => 'Features',
                        'EMAIL' => 'Email',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}