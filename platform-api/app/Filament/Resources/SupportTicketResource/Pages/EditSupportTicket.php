<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use App\Models\TicketMessage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reply')
                ->label('Répondre au client')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->form([
                    Forms\Components\Textarea::make('message')
                        ->label('Votre réponse')
                        ->required()
                        ->rows(4),
                ])
                ->action(function($data) {
                    TicketMessage::create([
                        'ticket_id' => $this->record->id,
                        'sender_id' => auth()->id(),
                        'sender_type' => 'ADMIN',
                        'message' => $data['message'],
                    ]);

                    $this->record->user->notify(new \App\Notifications\TicketReply(
                        $this->record->ticket_number,
                        $data['message']
                    ));

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Réponse envoyée au client')
                        ->send();
                        
                    // Refresh la page pour voir le nouveau message
                    return redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
        ];
    }
}