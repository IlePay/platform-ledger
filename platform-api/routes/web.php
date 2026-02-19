<?php

use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\MerchantController;
use App\Http\Controllers\Client\ProfileController;
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
// 2FA (public)
    Route::get('/2fa/verify', [\App\Http\Controllers\Client\TwoFactorController::class, 'showVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [\App\Http\Controllers\Client\TwoFactorController::class, 'verify'])->name('2fa.verify.submit');
    Route::post('/2fa/resend', [\App\Http\Controllers\Client\TwoFactorController::class, 'resend'])->name('2fa.resend');
// Client Dashboard (PROTECTED)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('client.logout');
    
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/transfer', [ClientDashboardController::class, 'transfer'])->name('client.transfer');
    Route::post('/transfer', [ClientDashboardController::class, 'sendMoney'])->name('client.transfer.send');
    
    Route::post('/pay/{qrCode}', [MerchantController::class, 'processPay'])->name('merchant.pay.process');
    Route::get('/merchant/dashboard', [MerchantController::class, 'dashboard'])->name('merchant.dashboard');
    Route::get('/merchant/qrcode', [MerchantController::class, 'downloadQrCode'])->name('merchant.qrcode');
    Route::get('/merchant/export', [MerchantController::class, 'export'])->name('merchant.export');
    Route::post('/merchant/transactions/{transaction}/refund', [MerchantController::class, 'refund'])->name('merchant.refund');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone');
    Route::post('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin');
    Route::post('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    
    // Security
    Route::post('/security/2fa/toggle', [\App\Http\Controllers\Client\TwoFactorController::class, 'toggle'])->name('security.2fa.toggle');
    Route::get('/security/history', [\App\Http\Controllers\Client\TwoFactorController::class, 'history'])->name('security.history');

    // Money Requests
    Route::get('/money-request/create', [\App\Http\Controllers\Client\MoneyRequestController::class, 'create'])->name('money-request.create');
    Route::post('/money-request', [\App\Http\Controllers\Client\MoneyRequestController::class, 'store'])->name('money-request.store');
    Route::get('/money-request/sent', [\App\Http\Controllers\Client\MoneyRequestController::class, 'sent'])->name('money-request.sent');
    Route::get('/money-request/received', [\App\Http\Controllers\Client\MoneyRequestController::class, 'received'])->name('money-request.received');
    Route::post('/money-request/{id}/accept', [\App\Http\Controllers\Client\MoneyRequestController::class, 'accept'])->name('money-request.accept');
    Route::post('/money-request/{id}/decline', [\App\Http\Controllers\Client\MoneyRequestController::class, 'decline'])->name('money-request.decline');

    // Contacts favoris
    Route::get('/contacts', [\App\Http\Controllers\Client\ContactsController::class, 'index'])->name('contacts.index');
    Route::post('/contacts/add', [\App\Http\Controllers\Client\ContactsController::class, 'add'])->name('contacts.add');
    Route::delete('/contacts/{id}', [\App\Http\Controllers\Client\ContactsController::class, 'remove'])->name('contacts.remove');
    Route::get('/contacts/{id}/quick-send', [\App\Http\Controllers\Client\ContactsController::class, 'quickSend'])->name('contacts.quick-send');

    // Notifications
    Route::post('/notifications/{id}/read', function($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return redirect()->back()->with('success', 'Notification marquée comme lue');
    })->name('notification.read');
    Route::get('/transactions/export', [ClientDashboardController::class, 'exportTransactions'])->name('transactions.export');
    Route::post('/notifications/mark-all-read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues');
    })->name('notifications.mark-all-read');
});