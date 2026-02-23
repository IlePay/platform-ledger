@extends('client.layout')
@section('title', 'Ticket #' . $ticket->ticket_number)
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <a href="{{ route('support.index') }}" class="text-white hover:text-secondary mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Ticket {{ $ticket->ticket_number }}</h1>
                    <p class="text-gray-200 mt-2">{{ $ticket->subject }}</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    @if($ticket->status === 'OPEN') bg-yellow-500
                    @elseif($ticket->status === 'RESOLVED') bg-green-500
                    @else bg-blue-500
                    @endif text-white">
                    {{ $ticket->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-4xl mx-auto">

            @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6">{{ session('success') }}</div>
            @endif

            <!-- Info ticket -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Catégorie</p>
                        <p class="font-semibold">{{ $ticket->category }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Priorité</p>
                        <p class="font-semibold">{{ $ticket->priority }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Créé le</p>
                        <p class="font-semibold">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                @if($ticket->status === 'RESOLVED' && $ticket->resolution_notes)
                <div class="mt-4 p-4 bg-green-50 rounded-lg">
                    <p class="text-sm font-semibold text-green-800 mb-2">✓ Résolu le {{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                    <p class="text-sm text-green-700">{{ $ticket->resolution_notes }}</p>
                </div>
                @endif
            </div>

            <!-- Messages -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="font-bold text-lg mb-4">Conversation</h2>
                
                <div class="space-y-4 mb-6">
                    @foreach($ticket->messages as $msg)
                    <div class="flex {{ $msg->sender_type === 'USER' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-lg {{ $msg->sender_type === 'USER' ? 'bg-primary text-white' : 'bg-gray-100' }} rounded-2xl p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas {{ $msg->sender_type === 'USER' ? 'fa-user' : 'fa-headset' }} text-sm"></i>
                                <span class="text-xs font-semibold">{{ $msg->sender_type === 'USER' ? 'Vous' : 'Support IlePay' }}</span>
                            </div>
                            <p class="text-sm">{{ $msg->message }}</p>
                            <p class="text-xs opacity-70 mt-2">{{ $msg->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Répondre -->
                @if($ticket->status !== 'CLOSED')
                <form action="{{ route('support.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="flex gap-3">
                        <input type="text" name="message" required placeholder="Votre message..." class="flex-1 px-4 py-3 border rounded-lg">
                        <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-dark">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                @else
                <p class="text-center text-gray-400 py-4">Ce ticket est fermé</p>
                @endif
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
// Auto-refresh toutes les 10 secondes
setInterval(() => {
    window.location.reload();
}, 10000);
</script>
@endpush
@endsection
