<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()->supportTickets()->orderBy('created_at', 'desc')->paginate(10);
        return view('client.support.index', compact('tickets'));
    }

    public function create()
    {
        return view('client.support.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:DISPUTE,REFUND,TECHNICAL,ACCOUNT,OTHER',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'transaction_id' => 'nullable|exists:transactions,id',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'user_id' => auth()->id(),
            'transaction_id' => $validated['transaction_id'] ?? null,
            'category' => $validated['category'],
            'priority' => 'MEDIUM',
            'subject' => $validated['subject'],
            'description' => $validated['description'],
        ]);

        // Premier message
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'USER',
            'message' => $validated['description'],
        ]);

        return redirect()->route('support.index')->with('success', "Ticket #{$ticket->ticket_number} créé !");
    }

    public function show($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        $ticket->load('messages');
        
        return view('client.support.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        
        $validated = $request->validate(['message' => 'required|string']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'USER',
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Message envoyé');
    }
}
