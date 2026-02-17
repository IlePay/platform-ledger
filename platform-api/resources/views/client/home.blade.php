@extends('client.layout')

@section('title', 'IlePay - Votre argent, simplement')

@section('content')
<!-- Hero Section -->
<div class="min-h-screen gradient-bg relative overflow-hidden">
    <!-- Navigation -->
    <nav class="container mx-auto px-6 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <img src="/images/ILEPAYHD.png" alt="IlePay" class="h-12">
            </div>
            <div class="hidden md:flex space-x-8 text-white">
                <a href="#features" class="hover:text-secondary transition">Fonctionnalités</a>
                <a href="#how-it-works" class="hover:text-secondary transition">Comment ça marche</a>
                <a href="#pricing" class="hover:text-secondary transition">Tarifs</a>
            </div>
            <div class="flex space-x-4">
                <a href="/login" class="text-white hover:text-secondary transition">Connexion</a>
                <a href="/register" class="bg-secondary text-primary px-6 py-2 rounded-full font-semibold hover:bg-secondary-light transition">
                    Créer un compte
                </a>
                <a href="/register/merchant" class="border-2 border-secondary text-white px-6 py-2 rounded-full font-semibold hover:bg-secondary hover:text-primary transition">
                    Compte Marchand
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Content -->
    <div class="container mx-auto px-6 pt-20 pb-32">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="text-white">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                    Votre argent,<br>
                    <span class="text-secondary">simplement</span>
                </h1>
                <p class="text-xl mb-8 text-gray-100">
                    Envoyez et recevez de l'argent instantanément, sans frais cachés. 
                    Rejoignez des milliers d'utilisateurs qui font confiance à IlePay.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/register" class="bg-secondary text-primary px-8 py-4 rounded-full font-semibold text-lg hover:bg-secondary-light transition text-center">
                        <i class="fas fa-rocket mr-2"></i>Commencer maintenant
                    </a>
                    <a href="#demo" class="border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-primary transition text-center">
                        <i class="fas fa-play-circle mr-2"></i>Voir la démo
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mt-16">
                    <div>
                        <div class="text-3xl font-bold">10K+</div>
                        <div class="text-gray-200">Utilisateurs</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold">24/7</div>
                        <div class="text-gray-200">Support</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold">0%</div>
                        <div class="text-gray-200">Frais cachés</div>
                    </div>
                </div>
            </div>

            <!-- Phone Mockup -->
            <div class="relative animate-float">
                <div class="glass rounded-3xl p-8 max-w-sm mx-auto shadow-2xl">
                    <div class="bg-white rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <p class="text-gray-500 text-sm">Solde disponible</p>
                                <p class="text-3xl font-bold text-gray-900">125,000 <span class="text-lg">XAF</span></p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-primary to-secondary rounded-full"></div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <button class="bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition">
                                <i class="fas fa-arrow-up mr-2"></i>Envoyer
                            </button>
                            <button class="bg-gray-100 text-gray-900 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                                <i class="fas fa-arrow-down mr-2"></i>Recevoir
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-arrow-down text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm">Reçu de Alice</p>
                                        <p class="text-xs text-gray-500">Aujourd'hui, 14:30</p>
                                    </div>
                                </div>
                                <span class="font-bold text-green-600">+15,000</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-arrow-up text-red-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm">Envoyé à Bob</p>
                                        <p class="text-xs text-gray-500">Hier, 09:15</p>
                                    </div>
                                </div>
                                <span class="font-bold text-red-600">-5,000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Decorative circles -->
    <div class="absolute top-20 right-10 w-72 h-72 bg-secondary rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
    <div class="absolute top-40 left-10 w-72 h-72 bg-primary-dark rounded-full mix-blend-multiply filter blur-xl opacity-40 animate-blob animation-delay-2000"></div>
</div>

<!-- Features Section -->
<div class="py-20 bg-white" id="features">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Pourquoi choisir IlePay ?</h2>
            <p class="text-xl text-gray-600">Une solution simple, sécurisée et rapide</p>
        </div>

        <div class="grid md:grid-cols-3 gap-12">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-bolt text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Instantané</h3>
                <p class="text-gray-600">Vos transferts arrivent en moins de 5 secondes, 24h/24 et 7j/7</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shield-alt text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">100% Sécurisé</h3>
                <p class="text-gray-600">Vos données sont cryptées et protégées par les normes bancaires</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-hand-holding-usd text-3xl text-secondary"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Sans frais</h3>
                <p class="text-gray-600">Aucun frais caché, des tarifs transparents et équitables</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="py-20 gradient-bg">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Prêt à commencer ?</h2>
        <p class="text-xl text-gray-100 mb-8">Créez votre compte gratuitement en moins de 2 minutes</p>
        <a href="/register" class="inline-block bg-secondary text-primary px-12 py-4 rounded-full font-semibold text-lg hover:bg-secondary-light transition">
            Ouvrir un compte
        </a>
    </div>
</div>

<style>
    @keyframes blob {
        0%, 100% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
</style>
@endsection