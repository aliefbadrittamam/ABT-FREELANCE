<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientInvoiceController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

// 1. Authentication Routes (Guest Only & Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 2. Telegram Webhook for Production
Route::post('/api/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

// 3. Public Customer/Client Invoice Portal (Standalone, No Auth/Sidebar required)
Route::get('/i/{token}', [ClientInvoiceController::class, 'show'])->name('client.invoices.show');
Route::get('/i/{token}/export/{format}', [ClientInvoiceController::class, 'export'])->name('client.invoices.export');
Route::get('/i/{token}/task-file', [ClientInvoiceController::class, 'downloadTaskFile'])->name('client.invoices.downloadTaskFile');

// 4. Public Live Monitoring Slot Turnamen (Tanpa Login / Siap Ngrok)
Route::get('/turnamen/efootball/live', function () {
    $activeTournaments = \App\Models\Tournament::with('participants')
        ->whereIn('status', ['open', 'full', 'ongoing'])
        ->latest('id')
        ->get();

    $settings = \App\Models\PaymentSetting::getSettings();
    $qrisPath = $settings->qris_image_path ? storage_path('app/public/' . $settings->qris_image_path) : null;
    $qrisBase64 = ($qrisPath && file_exists($qrisPath)) ? 'data:image/png;base64,' . base64_encode(file_get_contents($qrisPath)) : null;

    $bcaPath = storage_path('app/public/assets/banks/bca.png');
    $bcaBase64 = file_exists($bcaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bcaPath)) : null;

    $danaPath = storage_path('app/public/assets/banks/dana.png');
    $danaBase64 = file_exists($danaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($danaPath)) : null;

    $seaPath = storage_path('app/public/assets/banks/seabank.png');
    $seaBase64 = file_exists($seaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($seaPath)) : null;

    return view('tour-organizer.efootball.live', compact('activeTournaments', 'settings', 'qrisBase64', 'bcaBase64', 'danaBase64', 'seaBase64'));
})->name('tour-organizer.efootball.live');

// Endpoint JSON Real-time Polling untuk Halaman Publik (Auto-refresh tanpa reload)
Route::get('/turnamen/efootball/live/data', function () {
    $activeTournaments = \App\Models\Tournament::with('participants')
        ->whereIn('status', ['open', 'full', 'ongoing'])
        ->latest('id')
        ->get()
        ->map(function ($t) {
            $participantsMap = [];
            foreach ($t->participants as $p) {
                $participantsMap[$p->slot_number] = [
                    'team_name' => $p->team_name,
                    'is_winner' => (bool)$p->is_winner,
                ];
            }

            return [
                'id' => $t->id,
                'name' => $t->name,
                'session_label' => $t->session_label,
                'entry_fee' => (float)$t->entry_fee,
                'formatted_entry_fee' => number_format($t->entry_fee, 0, ',', '.'),
                'prize_pool' => (float)$t->prize_pool,
                'formatted_prize_pool' => number_format($t->prize_pool, 0, ',', '.'),
                'max_slots' => (int)$t->max_slots,
                'filled_slots_count' => (int)$t->filled_slots_count,
                'remaining_slots_count' => (int)$t->remaining_slots_count,
                'is_full' => $t->isFull(),
                'status' => $t->status,
                'participants' => $participantsMap,
            ];
        });

    return response()->json([
        'tournaments' => $activeTournaments,
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('tour-organizer.efootball.live.data');

// 5. Protected Admin Routes (Requires Authentication)
Route::middleware('auth')->group(function () {
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

    Route::get('/tour-organizer', [TournamentController::class, 'dashboard'])->name('tour-organizer.index');

    // Tournament Management Routes (eFootball Mobile)
    Route::prefix('tour-organizer/efootball')->name('tour-organizer.efootball.')->group(function () {
        Route::get('/', [TournamentController::class, 'index'])->name('index');
        Route::get('/create', [TournamentController::class, 'create'])->name('create');
        Route::post('/', [TournamentController::class, 'store'])->name('store');
        Route::post('/reset-sessions', [TournamentController::class, 'resetSessions'])->name('resetSessions');
        Route::get('/{tournament}', [TournamentController::class, 'show'])->name('show');
        Route::delete('/{tournament}', [TournamentController::class, 'destroy'])->name('destroy');
        Route::post('/{tournament}/register', [TournamentController::class, 'registerParticipant'])->name('register');
        Route::delete('/{tournament}/participants/{participant}', [TournamentController::class, 'removeParticipant'])->name('removeParticipant');
        Route::post('/{tournament}/start', [TournamentController::class, 'startTournament'])->name('start');
        Route::post('/{tournament}/winner/{participant}', [TournamentController::class, 'setWinner'])->name('setWinner');
        Route::post('/{tournament}/upload-prize-proof', [TournamentController::class, 'uploadPrizeProof'])->name('uploadPrizeProof');
        Route::post('/{tournament}/complete', [TournamentController::class, 'completeSession'])->name('complete');
    });

    // Alias for sidebar link
    Route::get('/tour-organizer/efootball-mobile', [TournamentController::class, 'index'])->name('tour-organizer.efootball');

    // Legacy redirect
    Route::get('/qris', fn() => redirect()->route('payment.index'));
});
