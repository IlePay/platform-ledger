@extends('client.layout')
@section('title', 'Mes Contacts')
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl font-bold">Mes Contacts</h1>
            <p class="text-gray-200 mt-2">Gérez vos contacts favoris</p>
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

            <!-- Ajouter contact -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="font-bold text-lg mb-4">Ajouter un favori</h2>
                <form action="{{ route('contacts.add') }}" method="POST" class="grid md:grid-cols-3 gap-4">
                    @csrf
                    <input type="tel" name="contact_phone" placeholder="Numéro de téléphone" required
                           class="px-4 py-3 border rounded-lg">
                    <input type="text" name="nickname" placeholder="Surnom (optionnel)"
                           class="px-4 py-3 border rounded-lg">
                    <button type="submit" class="bg-primary text-white px-4 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>Ajouter
                    </button>
                </form>
            </div>

            <!-- Favoris -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="font-bold text-lg mb-4">Favoris ({{ $favorites->count() }})</h2>
                @forelse($favorites as $fav)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($fav->first_name, 0, 1) }}{{ substr($fav->last_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold">{{ $fav->pivot->nickname ?: $fav->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $fav->phone }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('client.transfer') }}?phone={{ $fav->phone }}"
                           class="bg-primary text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-paper-plane mr-1"></i>Envoyer
                        </a>
                        <form action="{{ route('contacts.remove', $fav->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-400 py-8">Aucun favori</p>
                @endforelse
            </div>

            <!-- Suggérés -->
            @if($suggested->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-4">Contacts suggérés</h2>
                @foreach($suggested as $user)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-3">
                    <div>
                        <p class="font-semibold">{{ $user->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->transactions_received_count }} transactions</p>
                    </div>
                    <form action="{{ route('contacts.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="contact_phone" value="{{ $user->phone }}">
                        <button class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-plus mr-1"></i>Ajouter
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
