@extends('client.layout')
@section('title', 'Support')
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl font-bold">Support</h1>
            <p class="text-gray-200 mt-2">Mes tickets</p>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-4xl mx-auto">
            
            @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6">{{ session('success') }}</div>
            @endif

            <div class="mb-6">
                <a href="{{ route('support.create') }}" class="bg-primary text-white px-6 py-3 rounded-xl font-semibold inline-block">
                    <i class="fas fa-plus mr-2"></i>Nouveau ticket
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-4">Mes tickets</h2>
                
                @forelse($tickets as $ticket)
                <a href="{{ route('support.show', $ticket->id) }}" class="block p-4 border rounded-xl mb-3 hover:bg-gray-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-mono text-sm font-bold">{{ $ticket->ticket_number }}</span>
                                <span class="px-2 py-1 rounded-full text-xs
                                    @if($ticket->status === 'OPEN') bg-yellow-100 text-yellow-700
                                    @elseif($ticket->status === 'RESOLVED') bg-green-100 text-green-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ $ticket->status }}
                                </span>
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100">{{ $ticket->category }}</span>
                            </div>
                            <p class="font-semibold">{{ $ticket->subject }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                @empty
                <p class="text-center text-gray-400 py-8">Aucun ticket</p>
                @endforelse

                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
