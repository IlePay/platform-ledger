@extends('client.layout')

@section('title', 'Mon Compte - IlePay')

@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <div class="gradient-bg text-white">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <img src="/images/logo.png" alt="IlePay" class="h-10">
                <div class="flex items-center gap-4">
                    @if(auth()->user()->account_type === 'MERCHANT')
                    <a href="{{ route('merchant.dashboard') }}" class="text-white hover:text-secondary transition">
                        <i class="fas fa-store mr-1"></i>Dashboard Marchand
                    </a>
                    <a href="{{ route('profile.index') }}" class="text-white hover:text-secondary transition">
                        <i class="fas fa-user-circle mr-1"></i>Profil
                    </a>
                    @endif
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-white hover:text-secondary relative p-2">
                            <i class="fas fa-bell text-xl"></i>
                            @php $notifCount = auth()->user()->unreadNotifications->count(); @endphp
                            @if($notifCount > 0)
                            <span class="absolute top-0 right-0 w-5 h-5 bg-red-500 rounded-full text-xs flex items-center justify-center font-bold">
                                {{ $notifCount }}
                            </span>
                            @endif
                        </button>
                        
                        <!-- Dropdown notifications -->
                        <div x-show="open" 
                            @click.away="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl z-50 max-h-96 overflow-y-auto">
                            @if($notifCount > 0)
                                <div class="p-3 border-b flex items-center justify-between">
                                    <span class="font-semibold text-gray-900">{{ $notifCount }} notification(s)</span>
                                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-primary hover:text-primary-dark font-semibold">
                                            <i class="fas fa-check-double mr-1"></i>Tout marquer
                                        </button>
                                    </form>
                                </div>
                                @foreach(auth()->user()->unreadNotifications->take(5) as $notif)
                                <div class="p-4 border-b hover:bg-gray-50 transition relative group">
                                    <p class="font-semibold text-sm text-gray-900 pr-8">{{ $notif->data['title'] }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $notif->data['message'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                    <form action="{{ route('notification.read', $notif->id) }}" method="POST" class="absolute top-3 right-3">
                                        @csrf
                                        <button type="submit" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition opacity-0 group-hover:opacity-100">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            @else
                                <div class="p-6 text-center text-gray-400">
                                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                    <p class="text-sm">Aucune notification</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <form action="{{ route('client.logout') }}" method="POST">
                        @csrf
                        <button class="text-white hover:text-secondary transition">
                            <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Balance Card -->
        <div class="container mx-auto px-6 pb-24 pt-8">
            <div class="max-w-2xl mx-auto">
                <p class="text-gray-200 mb-2">Bonjour, {{ auth()->user()->first_name }} 👋</p>
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
                    <p class="text-gray-200 text-sm mb-2">Solde disponible</p>
                    <h1 class="text-5xl font-bold mb-6">
                        {{ $account ? number_format($account['balance'], 0, ',', ' ') : '0' }}
                        <span class="text-2xl">XAF</span>
                    </h1>

                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('client.transfer') }}"
                           class="bg-secondary text-primary px-6 py-3 rounded-xl font-semibold hover:bg-secondary-light transition text-center">
                            <i class="fas fa-paper-plane mr-2"></i>Envoyer
                        </a>
                        <button class="bg-white/20 text-white px-6 py-3 rounded-xl font-semibold hover:bg-white/30 transition">
                            <i class="fas fa-qrcode mr-2"></i>Recevoir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-12">
        <div class="max-w-2xl mx-auto">

            {{-- Message de succès --}}
            @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                {{ session('success') }}
            </div>
            @endif

            {{-- Notifications non lues --}}
            @php $unreadNotifs = auth()->user()->unreadNotifications->take(5); @endphp
            @if($unreadNotifs->count() > 0)
            <div class="mb-6 bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <i class="fas fa-bell text-primary"></i>
                        Notifications
                        <span class="w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">
                            {{ $unreadNotifs->count() }}
                        </span>
                    </h3>
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-primary hover:text-primary-dark font-semibold px-3 py-2 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-check-double mr-1"></i>Tout marquer comme lu
                        </button>
                    </form>
                </div>
                
                <div class="space-y-3">
                    @foreach($unreadNotifs as $notification)
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 flex-1">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-coins text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-gray-900">{{ $notification->data['title'] }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $notification->data['message'] }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <form action="{{ route('notification.read', $notification->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white hover:bg-red-500 rounded-full transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Stats rapides -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-500 text-sm">Total envoyé</p>
                    <p class="text-xl font-bold text-red-600">
                        {{ number_format($totalSent, 0, ',', ' ') }} XAF
                    </p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-500 text-sm">Total reçu</p>
                    <p class="text-xl font-bold text-green-600">
                        {{ number_format($totalReceived, 0, ',', ' ') }} XAF
                    </p>
                </div>
            </div>
            <!-- Recherche & Filtres -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div class="grid md:grid-cols-4 gap-4">
                        <input type="text" name="search" value="{{ $search }}" 
                            placeholder="Rechercher..." 
                            class="px-4 py-2 border rounded-lg">
                        
                        <select name="type" class="px-4 py-2 border rounded-lg">
                            <option value="">Tous types</option>
                            <option value="TRANSFER" {{ $type == 'TRANSFER' ? 'selected' : '' }}>Transfert</option>
                            <option value="REFUND" {{ $type == 'REFUND' ? 'selected' : '' }}>Remboursement</option>
                        </select>
                        
                        <select name="period" class="px-4 py-2 border rounded-lg">
                            <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Toute période</option>
                            <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Cette semaine</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Ce mois</option>
                        </select>
                        
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Rechercher
                        </button>
                    </div>
                </form>
                
                <div class="mt-4">
                    <a href="{{ route('transactions.export', ['period' => $period]) }}" 
                    class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-download mr-2"></i>Exporter CSV
                    </a>
                </div>
            </div>
            <!-- Transactions -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">Transactions récentes</h2>
                    <span class="text-gray-400 text-sm">{{ $transactions->total() }} au total</span>
                </div>

                @if($transactions->count() > 0)
                    <div class="space-y-3">
                        @foreach($transactions as $tx)
                        @php
                            $isSent = $tx->from_user_id === auth()->id();
                            $otherUser = $isSent ? $tx->toUser : $tx->fromUser;
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center
                                    {{ $isSent ? 'bg-red-100' : 'bg-green-100' }}">
                                    <i class="fas {{ $isSent ? 'fa-arrow-up text-red-600' : 'fa-arrow-down text-green-600' }}"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">
                                        {{ $isSent ? 'Envoyé à' : 'Reçu de' }}
                                        {{ $otherUser ? $otherUser->full_name : 'Inconnu' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $tx->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                    @if($tx->description)
                                    <p class="text-xs text-gray-400">{{ $tx->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg {{ $isSent ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $isSent ? '-' : '+' }}{{ number_format($tx->amount, 0, ',', ' ') }} XAF
                                </p>
                                <span class="text-xs px-2 py-1 rounded-full
                                    {{ $tx->status === 'COMPLETED' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $tx->status === 'COMPLETED' ? 'Complété' : $tx->status }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($transactions->hasPages())
                    <div class="mt-6">
                        {{ $transactions->links() }}
                    </div>
                    @endif

                @else
                    <div class="text-center py-12 text-gray-400">
                        <i class="fas fa-exchange-alt text-4xl mb-4"></i>
                        <p class="font-medium">Aucune transaction pour le moment</p>
                        <p class="text-sm mt-2">Commencez par envoyer de l'argent !</p>
                        <a href="{{ route('client.transfer') }}"
                           class="inline-block mt-4 bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-dark transition">
                            Faire un transfert
                        </a>
                    </div>
                @endif
            </div>

            <!-- Infos compte -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-500 text-sm">Limite quotidienne</p>
                    <p class="text-xl font-bold">
                        {{ number_format(auth()->user()->daily_limit, 0, ',', ' ') }} XAF
                    </p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-gray-500 text-sm">Niveau KYC</p>
                    <p class="text-xl font-bold">{{ auth()->user()->kyc_level }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection