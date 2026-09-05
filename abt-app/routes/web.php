<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientInvoiceController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

// Telegram Webhook for Production
Route::post('/api/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

// Public Customer/Client Invoice Portal (Standalone, No Auth/Sidebar required)
Route::get('/i/{token}', [ClientInvoiceController::class, 'show'])->name('client.invoices.show');
Route::get('/i/{token}/export/{format}', [ClientInvoiceController::class, 'export'])->name('client.invoices.export');
Route::get('/i/{token}/task-file', [ClientInvoiceController::class, 'downloadTaskFile'])->name('client.invoices.downloadTaskFile');

// Public Live Monitoring Slot Turnamen (Tanpa Login / Siap Ngrok)
Route::get('/turnamen/efootball/live', function () {
    $activeTournaments = \App\Models\Tournament::with('participants')
        ->whereIn('status', ['open', 'full', 'ongoing'])
        ->latest('id')
        ->get();

    return view('tour-organizer.efootball.live', compact('activeTournaments'));
})->name('tour-organizer.efootball.live');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

Route::resource('invoices', InvoiceController::class);
Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
Route::post('/invoices/{invoice}/toggle-payout', [InvoiceController::class, 'togglePayout'])->name('invoices.togglePayout');
Route::get('/invoices/{invoice}/export/{format}', [InvoiceController::class, 'export'])->name('invoices.export');
Route::post('/invoices/{invoice}/task-file', [InvoiceController::class, 'uploadTaskFile'])->name('invoices.uploadTaskFile');
Route::get('/invoices/{invoice}/task-file', [InvoiceController::class, 'downloadTaskFile'])->name('invoices.downloadTaskFile');
Route::delete('/invoices/{invoice}/task-file', [InvoiceController::class, 'deleteTaskFile'])->name('invoices.deleteTaskFile');

Route::resource('testimonials', TestimonialController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::post('/testimonials/{id}/restore', [TestimonialController::class, 'restore'])->name('testimonials.restore');
Route::delete('/testimonials/{id}/force-delete', [TestimonialController::class, 'forceDelete'])->name('testimonials.forceDelete');

Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
Route::post('/payment', [PaymentController::class, 'update'])->name('payment.update');

Route::get('/tour-organizer', fn() => view('tour-organizer.index'))->name('tour-organizer.index');

// Tournament Management Routes (eFootball Mobile)
Route::prefix('tour-organizer/efootball')->name('tour-organizer.efootball.')->group(function () {
    Route::get('/', [TournamentController::class, 'index'])->name('index');
    Route::get('/create', [TournamentController::class, 'create'])->name('create');
    Route::post('/', [TournamentController::class, 'store'])->name('store');
    Route::get('/{tournament}', [TournamentController::class, 'show'])->name('show');
    Route::delete('/{tournament}', [TournamentController::class, 'destroy'])->name('destroy');
    Route::post('/{tournament}/register', [TournamentController::class, 'registerParticipant'])->name('register');
    Route::delete('/{tournament}/participants/{participant}', [TournamentController::class, 'removeParticipant'])->name('removeParticipant');
    Route::post('/{tournament}/winner/{participant}', [TournamentController::class, 'setWinner'])->name('setWinner');
    Route::post('/{tournament}/upload-prize-proof', [TournamentController::class, 'uploadPrizeProof'])->name('uploadPrizeProof');
    Route::post('/{tournament}/complete', [TournamentController::class, 'completeSession'])->name('complete');
});

// Alias for sidebar link
Route::get('/tour-organizer/efootball-mobile', [TournamentController::class, 'index'])->name('tour-organizer.efootball');

// Legacy redirect
Route::get('/qris', fn() => redirect()->route('payment.index'));
