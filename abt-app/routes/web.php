<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientInvoiceController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Public Customer/Client Invoice Portal (Standalone, No Auth/Sidebar required)
Route::get('/i/{token}', [ClientInvoiceController::class, 'show'])->name('client.invoices.show');
Route::get('/i/{token}/export/{format}', [ClientInvoiceController::class, 'export'])->name('client.invoices.export');
Route::get('/i/{token}/task-file', [ClientInvoiceController::class, 'downloadTaskFile'])->name('client.invoices.downloadTaskFile');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

Route::resource('invoices', InvoiceController::class);
Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
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
Route::get('/tour-organizer/efootball-mobile', fn() => view('tour-organizer.efootball'))->name('tour-organizer.efootball');

// Legacy redirect
Route::get('/qris', fn() => redirect()->route('payment.index'));
