@extends('client.layout')
<!-- QR Code -->
<div class="bg-white rounded-2xl shadow-lg p-6 text-center">
    <h3 class="font-bold text-lg mb-4">Mon QR Code</h3>
    
    <div class="w-48 h-48 bg-white border-4 border-primary rounded-xl mx-auto mb-4 flex items-center justify-center p-2">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url('/pay/' . $user->qr_code)) }}&color=2D4B9E" 
     alt="QR Code {{ $user->qr_code }}"
     class="w-44 h-44 mx-auto">
    </div>
    
    <p class="text-gray-500 text-sm mb-4">
        Code: <span class="font-mono font-bold text-primary">{{ $user->qr_code }}</span>
    </p>
    
    <div class="space-y-3">
        <a href="{{ route('merchant.pay', $user->qr_code) }}" 
           target="_blank"
           class="block w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition text-center">
            <i class="fas fa-external-link-alt mr-2"></i>Page de paiement
        </a>
        
        <a href="{{ route('merchant.qrcode') }}"
           class="block w-full bg-secondary text-primary py-3 rounded-xl font-semibold hover:bg-secondary-light transition text-center">
            <i class="fas fa-download mr-2"></i>Télécharger QR Code
        </a>
        
        <button onclick="copyPaymentLink()"
           class="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition text-center">
            <i class="fas fa-copy mr-2"></i>Copier le lien
        </button>
    </div>
</div>