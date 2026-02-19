@extends('client.layout')
@section('title', 'Historique de connexion')
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="gradient-bg text-white pb-24 pt-8">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl font-bold">Historique de connexion</h1>
            <p class="text-gray-200 mt-2">Suivez l'activité de votre compte</p>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-4">Connexions récentes</h2>
                
                @forelse($history as $login)
                <div class="flex items-start justify-between p-4 border-b last:border-0 {{ !$login->was_successful ? 'bg-red-50' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $login->was_successful ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas {{ $login->was_successful ? 'fa-check text-green-600' : 'fa-times text-red-600' }}"></i>
                        </div>
                        <div>
                            <p class="font-semibold">
                                {{ $login->was_successful ? 'Connexion réussie' : 'Tentative échouée' }}
                            </p>
                            <p class="text-sm text-gray-600">{{ $login->device_type }} • {{ $login->ip_address }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $login->created_at->format('d/m/Y à H:i') }}</p>
                            @if(!$login->was_successful && $login->failure_reason)
                            <p class="text-xs text-red-600 mt-1">{{ $login->failure_reason }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($login->created_at->isToday())
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">Aujourd'hui</span>
                    @endif
                </div>
                @empty
                <p class="text-center text-gray-400 py-8">Aucun historique</p>
                @endforelse

                @if($history->hasPages())
                <div class="mt-6">{{ $history->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
