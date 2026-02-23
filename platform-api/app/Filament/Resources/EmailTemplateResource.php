<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Email Templates';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->label('Clé unique')
                ->required()
                ->unique(ignoreRecord: true)
                ->disabled(fn($record) => $record !== null),
            Forms\Components\TextInput::make('name')
                ->label('Nom')
                ->required(),
            Forms\Components\TextInput::make('subject')
                ->label('Sujet')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Textarea::make('body')
                ->label('Corps du message')
                ->required()
                ->rows(10)
                ->columnSpanFull()
                ->helperText('Utilisez {{ variable }} pour insérer des variables'),
            Forms\Components\TagsInput::make('variables')
                ->label('Variables disponibles')
                ->placeholder('Tapez et appuyez sur Entrée')
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')
                ->label('Actif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}