<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\WalletController;
use App\Http\Controllers\Dashboard\TicketController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Public routes ──────────────────────────────────────────────────────
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/portfolio', [PublicController::class, 'portfolio'])->name('portfolio');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');

// ── Stripe webhook (no CSRF) ───────────────────────────────────────────
Route::post('/webhook/stripe', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhook.stripe');

// ── PayPal webhook (no CSRF) ──────────────────────────────────────────
Route::post('/webhook/paypal', [PaymentController::class, 'paypalWebhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhook.paypal');

// ── Crypto / NowPayments IPN (no CSRF) ────────────────────────────────
Route::post('/webhook/crypto', [PaymentController::class, 'cryptoWebhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('payment.crypto.webhook');

// ── Auth routes (Breeze) ───────────────────────────────────────────────
require __DIR__.'/auth.php';

// ── Authenticated dashboard ───────────────────────────────────────────
Route::middleware(['auth', 'verified', \App\Http\Middleware\BannedMiddleware::class])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/revision', [OrderController::class, 'requestRevision'])->name('orders.revision');

        // Payment checkout
        Route::post('/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');

        // Wallet
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');

        // Tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/kyc', [ProfileController::class, 'uploadKyc'])->name('profile.kyc');
    });
