<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\MerchantController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('client.home');
});

// Client Auth (PUBLIC)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('client.login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('client.register');
Route::post('/register', [AuthController::class, 'register'])->name('client.register.submit');
Route::get('/register/merchant', [AuthController::class, 'showRegisterMerchant'])->name('client.register.merchant');
Route::post('/register/merchant', [AuthController::class, 'registerMerchant'])->name('client.register.merchant.submit');

// Page paiement publique (scan QR)
Route::get('/pay/{qrCode}', [MerchantController::class, 'paymentPage'])->name('merchant.pay');


Route::middleware('auth')->get('/api/notifications/check', function() {
    $notifications = auth()->user()->unreadNotifications->take(5);
    return response()->json([
        'count' => $notifications->count(),
        'notifications' => $notifications->map(fn($n) => [
            'title' => $n->data['title'],
            'message' => $n->data['message'],
        ])
    ]);
});

// Client Dashboard (PROTECTED)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('client.logout');
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/transfer', [ClientDashboardController::class, 'transfer'])->name('client.transfer');
    Route::post('/transfer', [ClientDashboardController::class, 'sendMoney'])->name('client.transfer.send');
    Route::post('/pay/{qrCode}', [MerchantController::class, 'processPay'])->name('merchant.pay.process');
    Route::get('/merchant/dashboard', [MerchantController::class, 'dashboard'])->name('merchant.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/merchant/qrcode', [MerchantController::class, 'downloadQrCode'])->name('merchant.qrcode');
    Route::get('/merchant/export', [MerchantController::class, 'export'])->name('merchant.export');
    Route::post('/merchant/transactions/{transaction}/refund', [MerchantController::class, 'refund'])->name('merchant.refund');
    Route::post('/notifications/{id}/read', function($id) {
    $notification = auth()->user()->notifications()->findOrFail($id);
    $notification->markAsRead();
    return redirect()->back()->with('success', 'Notification marquée comme lue');
        }   )->name('notification.read');

    Route::post('/notifications/mark-all-read', function() {
    auth()->user()->unreadNotifications->markAsRead();
    return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues');
        })->name('notifications.mark-all-read');
});