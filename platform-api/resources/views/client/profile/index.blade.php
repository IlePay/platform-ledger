@extends('client.layout')

@section('title', 'Mon Profil - IlePay')

@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <div class="gradient-bg text-white">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <img src="/images/logo.png" alt="IlePay" class="h-10">
                <div class="flex items-center gap-6">
                    <a href="{{ route('client.dashboard') }}" class="text-white hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-1"></i>Retour
                    </a>
                </div>
            </div>
        </nav>

        <div class="container mx-auto px-6 pb-24 pt-8">
            <h1 class="text-3xl font-bold">Mon Profil</h1>
            <p class="text-gray-200 mt-2">Gérez vos informations personnelles</p>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-16">
        <div class="max-w-4xl mx-auto">

            @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <!-- Photo & Infos -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-6">Informations personnelles</h2>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Avatar -->
                    <div class="flex items-center gap-6 mb-6">
                        <div class="relative">
                            <div id="avatar-preview">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" 
                                        alt="Avatar" 
                                        class="w-24 h-24 rounded-full object-cover border-4 border-primary">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-primary flex items-center justify-center text-white text-3xl font-bold border-4 border-primary">
                                        {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <label for="avatar" class="absolute bottom-0 right-0 bg-secondary p-2 rounded-full cursor-pointer hover:bg-secondary-light transition">
                                <i class="fas fa-camera text-primary"></i>
                            </label>
                            <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*">
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">{{ $user->full_name }}</h3>
                            <p class="text-gray-500">{{ $user->phone }}</p>
                            <p class="text-sm text-gray-400">Membre depuis {{ $user->created_at->format('M Y') }}</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Prénom</label>
                            <input type="text" name="first_name" value="{{ $user->first_name }}" 
                                   class="w-full px-4 py-3 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Nom</label>
                            <input type="text" name="last_name" value="{{ $user->last_name }}" 
                                   class="w-full px-4 py-3 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}" 
                                   class="w-full px-4 py-3 border rounded-lg"
                                   placeholder="exemple@email.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Type de compte</label>
                            <input type="text" value="{{ $user->account_type }}" 
                                   class="w-full px-4 py-3 border rounded-lg bg-gray-100" readonly>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">
                        <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                    </button>
                </form>
            </div>

            <!-- Changer Téléphone -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Numéro de téléphone</h2>
                <form action="{{ route('profile.phone') }}" method="POST">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Nouveau numéro</label>
                            <input type="tel" name="phone" value="{{ $user->phone }}" 
                                   class="w-full px-4 py-3 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Confirmer avec PIN</label>
                            <input type="password" name="pin" 
                                   class="w-full px-4 py-3 border rounded-lg" 
                                   placeholder="****" required>
                        </div>
                    </div>
                    <button type="submit" class="mt-4 bg-yellow-500 text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-phone mr-2"></i>Changer le numéro
                    </button>
                </form>
            </div>

            <!-- Changer PIN -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Code PIN</h2>
                <form action="{{ route('profile.pin') }}" method="POST">
                    @csrf
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">PIN actuel</label>
                            <input type="password" name="current_pin" 
                                   class="w-full px-4 py-3 border rounded-lg" 
                                   placeholder="****" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Nouveau PIN</label>
                            <input type="password" name="new_pin" 
                                   class="w-full px-4 py-3 border rounded-lg" 
                                   placeholder="****" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Confirmer nouveau PIN</label>
                            <input type="password" name="new_pin_confirmation" 
                                   class="w-full px-4 py-3 border rounded-lg" 
                                   placeholder="****" required>
                        </div>
                    </div>
                    <button type="submit" class="mt-4 bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-lock mr-2"></i>Changer le PIN
                    </button>
                </form>
            </div>

            <!-- Notifications -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Préférences de notifications</h2>
                <form action="{{ route('profile.notifications') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-sms text-primary text-xl"></i>
                                <div>
                                    <p class="font-semibold">Notifications SMS</p>
                                    <p class="text-sm text-gray-500">Recevoir des SMS pour les transactions</p>
                                </div>
                            </div>
                            <input type="checkbox" name="sms_notifications" value="1" 
                                   {{ $user->sms_notifications ? 'checked' : '' }}
                                   class="w-6 h-6 text-primary">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-envelope text-primary text-xl"></i>
                                <div>
                                    <p class="font-semibold">Notifications Email</p>
                                    <p class="text-sm text-gray-500">Recevoir des emails pour les transactions</p>
                                </div>
                            </div>
                            <input type="checkbox" name="email_notifications" value="1" 
                                   {{ $user->email_notifications ? 'checked' : '' }}
                                   class="w-6 h-6 text-primary">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-bell text-primary text-xl"></i>
                                <div>
                                    <p class="font-semibold">Notifications Push</p>
                                    <p class="text-sm text-gray-500">Recevoir des notifications sur l'application</p>
                                </div>
                            </div>
                            <input type="checkbox" name="push_notifications" value="1" 
                                   {{ $user->push_notifications ? 'checked' : '' }}
                                   class="w-6 h-6 text-primary">
                        </label>
                    </div>
                    <button type="submit" class="mt-4 bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">
                        <i class="fas fa-save mr-2"></i>Enregistrer les préférences
                    </button>
                </form>
            </div>
            
            <!-- Sécurité 2FA -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Sécurité</h2>
                
                <!-- 2FA Toggle -->
                <div class="p-4 border rounded-xl mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-alt text-primary text-2xl"></i>
                            <div>
                                <p class="font-semibold">Authentification à deux facteurs</p>
                                <p class="text-sm text-gray-500">Code SMS à chaque connexion</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->two_factor_enabled)
                            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full">Activée</span>
                            @else
                            <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full">Désactivée</span>
                            @endif
                        </div>
                    </div>
                    
                    <form action="{{ route('security.2fa.toggle') }}" method="POST" class="flex gap-3 items-end">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-2">Confirmer avec PIN</label>
                            <input type="password" name="pin" required 
                                class="w-full px-4 py-3 border rounded-lg" 
                                placeholder="****">
                        </div>
                        <button type="submit" 
                                class="px-6 py-3 rounded-lg font-semibold {{ $user->two_factor_enabled ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white">
                            {{ $user->two_factor_enabled ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>

                <!-- Historique connexions -->
                <a href="{{ route('security.history') }}" 
                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-history text-gray-600 text-xl"></i>
                        <div>
                            <p class="font-semibold">Historique de connexion</p>
                            <p class="text-sm text-gray-500">Voir toutes vos connexions</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </a>
            </div>
            
            <!-- Limites & KYC -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Limites de transaction</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600">Limite quotidienne</p>
                        <p class="text-2xl font-bold text-primary">
                            {{ number_format($user->daily_limit, 0, ',', ' ') }} XAF
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Niveau {{ $user->kyc_level }}</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-600">Limite mensuelle</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ number_format($user->monthly_limit, 0, ',', ' ') }} XAF
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Niveau {{ $user->kyc_level }}</p>
                    </div>
                </div>
                @if($user->kyc_level !== 'PREMIUM')
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="font-semibold text-yellow-800">🚀 Augmentez vos limites !</p>
                    <p class="text-sm text-yellow-700 mt-1">
                        Passez au niveau {{ $user->kyc_level === 'BASIC' ? 'STANDARD' : 'PREMIUM' }} 
                        pour des limites plus élevées
                    </p>
                    <a href="#" class="inline-block mt-3 bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-600 transition">
                        Upgrade maintenant
                    </a>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Preview avatar avant upload
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('avatar-preview').innerHTML = 
                `<img src="${event.target.result}" class="w-24 h-24 rounded-full object-cover border-4 border-primary">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
