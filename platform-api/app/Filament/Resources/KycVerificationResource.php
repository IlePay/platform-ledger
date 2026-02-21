<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycVerificationResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class KycVerificationResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'KYC Verification';
    protected static ?string $navigationGroup = 'Compliance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations utilisateur')->schema([
                Forms\Components\TextInput::make('full_name')->label('Nom complet')->disabled(),
                Forms\Components\TextInput::make('phone')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
            ])->columns(3),

            Forms\Components\Section::make('Documents KYC')->schema([
                Forms\Components\FileUpload::make('id_document_path')
                    ->label('Pièce d\'identité')
                    ->image()
                    ->imageEditor()
                    ->downloadable(),
                Forms\Components\FileUpload::make('proof_address_path')
                    ->label('Justificatif de domicile')
                    ->image()
                    ->downloadable(),
            ])->columns(2),

            Forms\Components\Section::make('Vérification')->schema([
                Forms\Components\Select::make('kyc_status')
                    ->options([
                        'PENDING' => 'En attente',
                        'APPROVED' => 'Approuvé',
                        'REJECTED' => 'Rejeté',
                    ])
                    ->required(),
                Forms\Components\Select::make('kyc_level')
                    ->options([
                        'BASIC' => 'BASIC (50K/jour)',
                        'STANDARD' => 'STANDARD (500K/jour)',
                        'PREMIUM' => 'PREMIUM (5M/jour)',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('kyc_rejection_reason')
                    ->label('Raison du rejet')
                    ->visible(fn($get) => $get('kyc_status') === 'REJECTED'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\BadgeColumn::make('kyc_status')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'APPROVED',
                        'danger' => 'REJECTED',
                    ]),
                Tables\Columns\BadgeColumn::make('kyc_level'),
                Tables\Columns\ImageColumn::make('id_document_path')->label('ID'),
                Tables\Columns\ImageColumn::make('proof_address_path')->label('Proof'),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kyc_status')
                    ->options([
                        'PENDING' => 'En attente',
                        'APPROVED' => 'Approuvé',
                        'REJECTED' => 'Rejeté',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->kyc_status === 'PENDING')
                    ->action(function($record) {
                        $record->update([
                            'kyc_status' => 'APPROVED',
                            'kyc_verified_at' => now(),
                            'kyc_verified_by' => auth()->id(),
                        ]);
                        Notification::make()->success()->title('KYC approuvé')->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->kyc_status === 'PENDING')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Raison')->required(),
                    ])
                    ->action(function($record, $data) {
                        $record->update([
                            'kyc_status' => 'REJECTED',
                            'kyc_rejection_reason' => $data['reason'],
                        ]);
                        Notification::make()->danger()->title('KYC rejeté')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKycVerifications::route('/'),
            'edit' => Pages\EditKycVerification::route('/{record}/edit'),
        ];
    }
}
