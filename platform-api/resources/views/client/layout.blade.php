<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'IlePay - L\'immobilier géré autrement')</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com/3.4.0/tailwind.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2D4B9E',
                        secondary: '#F9B233',
                        'primary-dark': '#1e3270',
                        'secondary-light': '#fcc866',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #2D4B9E 0%, #1e3270 100%);
        }
        
        .gradient-accent {
            background: linear-gradient(135deg, #2D4B9E 0%, #F9B233 100%);
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
     @auth
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2">
                     <img src="/images/ILEPAYHD.png" alt="IlePay" class="h-10">
                </a>
                
                <div class="flex items-center gap-4">
                    @if(auth()->user()->account_type === 'MERCHANT')
                    <a href="{{ route('merchant.dashboard') }}" class="text-gray-600 hover:text-primary">
                        <i class="fas fa-store"></i>
                    </a>
                    @endif
                    
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative">
                            <i class="fas fa-bell text-2xl text-gray-600 hover:text-primary"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                            @endif
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl max-h-96 overflow-y-auto">
                            <div class="p-4 border-b flex items-center justify-between">
                                <h3 class="font-bold">Notifications</h3>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-primary hover:text-primary-dark font-semibold">
                                        <i class="fas fa-check-double mr-1"></i>Tout marquer
                                    </button>
                                </form>
                                @endif
                            </div>
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notif)
                            <div class="relative border-b hover:bg-gray-50">
                                <a href="@if($notif->data['type'] === 'MONEY_REQUESTED') {{ route('money-request.received') }} @else {{ route('client.dashboard') }} @endif"
                                @click="open = false"
                                class="block px-4 py-3 pr-12">
                                    <p class="font-semibold text-sm">{{ $notif->data['title'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $notif->data['message'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                </a>
                                <form action="{{ route('notification.read', $notif->id) }}" method="POST" class="absolute top-3 right-3">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center hover:bg-red-100 rounded-full transition z-10">
                                        <i class="fas fa-times text-red-500"></i>
                                    </button>
                                </form>
                            </div>
                            @empty
                            <div class="p-8 text-center text-gray-400">Aucune notification</div>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Profil -->
                    <a href="{{ route('profile.index') }}" class="text-gray-600 hover:text-primary">
                        <i class="fas fa-user-circle text-2xl"></i>
                    </a>
                    
                    <!-- Déconnexion -->
                    <form action="{{ route('client.logout') }}" method="POST" class="inline">
                        @csrf
                        <button class="text-gray-600 hover:text-red-500">
                            <i class="fas fa-sign-out-alt text-2xl"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth   
    @yield('content')
    @stack('scripts')
</body>
</html>