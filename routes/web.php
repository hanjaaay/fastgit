<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TouristAttractionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Frontend Festigo)
|--------------------------------------------------------------------------
*/
Route::get('/', [TouristAttractionController::class, 'index'])->name('home');
Route::get('/attractions', [TouristAttractionController::class, 'index'])->name('attractions.index');
Route::get('/attractions/{attraction}', [TouristAttractionController::class, 'show'])->name('attractions.show');

// Rute untuk Midtrans callback. HARUS berada di luar middleware 'auth'
// agar bisa diakses oleh server Midtrans.
Route::post('/midtrans-callback', [BookingController::class, 'midtransCallback']);

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Profile & Booking)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Rute Dashboard & Profile
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rute Booking yang memerlukan autentikasi
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/attractions/{attraction}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/attractions/{attraction}/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    
    // Rute untuk mendownload tiket
    Route::get('/bookings/{booking}/ticket', [BookingController::class, 'downloadTicket'])->name('bookings.ticket');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';