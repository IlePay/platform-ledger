<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Support Tickets';
    protected static ?string $navigationGroup = 'Support';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Ticket Info')->schema([
                Forms\Components\TextInput::make('ticket_number')->disabled(),
                Forms\Components\TextInput::make('user.full_name')->label('User')->disabled(),
                Forms\Components\Select::make('category')
                    ->options([
                        'DISPUTE' => 'Litige',
                        'REFUND' => 'Remboursement',
                        'TECHNICAL' => 'Technique',
                        'ACCOUNT' => 'Compte',
                        'OTHER' => 'Autre',
                    ])
                    ->disabled(),
                Forms\Components\Select::make('priority')
                    ->options([
                        'LOW' => 'Basse',
                        'MEDIUM' => 'Moyenne',
                        'HIGH' => 'Haute',
                        'URGENT' => 'Urgente',
                    ])
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Description')->schema([
                Forms\Components\TextInput::make('subject')->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('description')->disabled()->rows(4)->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Résolution')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'OPEN' => 'Ouvert',
                        'IN_PROGRESS' => 'En cours',
                        'RESOLVED' => 'Résolu',
                        'CLOSED' => 'Fermé',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('resolution_notes')
                    ->label('Notes de résolution')
                    ->rows(3),
            ])->columns(1),

            Forms\Components\Section::make('Conversation')->schema([
            Forms\Components\Repeater::make('messages')
                ->relationship()
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('sender_type')
                            ->label('De')
                            ->formatStateUsing(fn($state) => $state === 'USER' ? 'Client' : 'Support')
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Date')
                            ->disabled(),
                    ]),
                    Forms\Components\Textarea::make('message')
                        ->label('Message')
                        ->disabled()
                        ->rows(2),
                ])
                ->addable(false)
                ->deletable(false)
                ->columnSpanFull()
                ->collapsed(),
            
            // Ajoute réponse admin
            Forms\Components\Textarea::make('admin_reply')
                ->label('Répondre au client')
                ->rows(3)
                ->placeholder('Écrivez votre réponse...')
                ->helperText('Cette réponse sera envoyée au client')
                ->dehydrated(false), // Ne pas sauvegarder dans le ticket
        ])->columns(1),
        ]);
    }

        public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'danger' => 'DISPUTE',
                        'warning' => 'REFUND',
                        'primary' => 'TECHNICAL',
                        'success' => 'ACCOUNT',
                    ]),
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'success' => 'LOW',
                        'warning' => 'MEDIUM',
                        'danger' => 'HIGH',
                        'danger' => 'URGENT',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'OPEN',
                        'primary' => 'IN_PROGRESS',
                        'success' => 'RESOLVED',
                        'secondary' => 'CLOSED',
                    ]),
                Tables\Columns\TextColumn::make('subject')
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'OPEN' => 'Ouvert',
                        'IN_PROGRESS' => 'En cours',
                        'RESOLVED' => 'Résolu',
                        'CLOSED' => 'Fermé',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'URGENT' => 'Urgent',
                        'HIGH' => 'Haute',
                        'MEDIUM' => 'Moyenne',
                        'LOW' => 'Basse',
                    ]),
                Tables\Filters\SelectFilter::make('category'),
            ])
            ->actions([
                Tables\Actions\Action::make('reply')
                    ->label('Répondre')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->label('Votre réponse')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function($record, $data) {
                        \App\Models\TicketMessage::create([
                            'ticket_id' => $record->id,
                            'sender_id' => auth()->id(),
                            'sender_type' => 'ADMIN',
                            'message' => $data['message'],
                        ]);

                        $record->user->notify(new \App\Notifications\TicketReply(
                            $record->ticket_number,
                            $data['message']
                        ));

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Réponse envoyée')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'RESOLVED')
                    ->form([
                        Forms\Components\Textarea::make('notes')->label('Notes de résolution')->required(),
                    ])
                    ->action(function($record, $data) {
                        $record->update([
                            'status' => 'RESOLVED',
                            'resolved_at' => now(),
                            'resolution_notes' => $data['notes'],
                        ]);
                        Notification::make()->success()->title('Ticket résolu')->send();
                    }),
                Tables\Actions\Action::make('force_refund')
                    ->label('Remboursement forcé')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) => $record->transaction_id && $record->category === 'DISPUTE')
                    ->requiresConfirmation()
                    ->action(function($record) {
                        Notification::make()->success()->title('Remboursement effectué')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'OPEN')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}