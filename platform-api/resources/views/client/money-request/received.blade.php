@extends('client.layout')
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl font-bold">Demandes reçues</h1>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-4xl mx-auto">
            @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6">{{ session('success') }}</div>
            @endif
            @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6">{{ $errors->first() }}</div>
            @endif

            @forelse($requests as $req)
            <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-bold">{{ $req->requester->full_name }}</p>
                        <p class="text-2xl font-bold text-primary">{{ number_format($req->amount, 0) }} XAF</p>
                        @if($req->message)
                        <p class="text-gray-500 text-sm mt-2">{{ $req->message }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-2">{{ $req->created_at->diffForHumans() }}</p>
                    </div>

                    @if($req->isPending() && !$req->isExpired())
                    <div class="flex gap-2">
                        <button onclick="showAcceptModal({{ $req->id }}, {{ $req->amount }})"
                                class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-600">
                            ✓ Accepter
                        </button>
                        <form action="{{ route('money-request.decline', $req->id) }}" method="POST">
                            @csrf
                            <button class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-600">
                                ✕ Refuser
                            </button>
                        </form>
                    </div>
                    @else
                    <span class="px-3 py-1 rounded-full text-sm
                        @if($req->status === 'ACCEPTED') bg-green-100 text-green-700
                        @elseif($req->status === 'DECLINED') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ $req->status }}
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
                <i class="fas fa-inbox text-4xl mb-4"></i>
                <p>Aucune demande reçue</p>
            </div>
            @endforelse

            <div class="mt-6">{{ $requests->links() }}</div>
        </div>
    </div>
</div>

<!-- Modal Accept -->
<div id="acceptModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">Confirmer le paiement</h3>
        <form id="acceptForm" method="POST">
            @csrf
            <p class="mb-4">Montant: <span id="modalAmount" class="font-bold text-primary"></span> XAF</p>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Code PIN</label>
                <input type="password" name="pin" required class="w-full px-4 py-3 border rounded-lg">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeAcceptModal()"
                        class="flex-1 px-4 py-3 bg-gray-100 rounded-lg">Annuler</button>
                <button type="submit"
                        class="flex-1 px-4 py-3 bg-green-500 text-white rounded-lg">Payer</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAcceptModal(id, amount) {
    document.getElementById('acceptModal').classList.remove('hidden');
    document.getElementById('modalAmount').textContent = amount.toLocaleString();
    document.getElementById('acceptForm').action = `/money-request/${id}/accept`;
}
function closeAcceptModal() {
    document.getElementById('acceptModal').classList.add('hidden');
}
</script>
@endsection
