@extends('client.layout')
@section('title', 'Vérification 2FA')
@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Vérification 2FA</h1>
            <p class="text-gray-500 mt-2">Entrez le code envoyé par SMS</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6">{{ session('success') }}</div>
        @endif

        <form action="{{ route('2fa.verify.submit') }}" method="POST">
            @csrf
            <div class="mb-6">
                <input type="text" name="code" maxlength="6" required autofocus
                       class="w-full px-4 py-4 border-2 rounded-xl text-center text-2xl font-bold tracking-widest"
                       placeholder="000000">
            </div>

            <button type="submit" class="w-full bg-primary text-white py-4 rounded-xl font-bold hover:bg-primary-dark transition">
                Vérifier
            </button>
        </form>

        <form action="{{ route('2fa.resend') }}" method="POST" class="mt-4">
            @csrf
            <button class="w-full text-gray-600 hover:text-primary text-sm">
                Renvoyer le code
            </button>
        </form>
    </div>
</div>
@endsection
