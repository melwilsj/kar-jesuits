<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\FirebaseAuthController;

// Public routes
Route::get('/', App\Livewire\Welcome::class)->name('welcome');

// Guest routes
Route::middleware('guest')->group(function () {
    // Firebase User Authentication
    Route::get('/login', [FirebaseAuthController::class, 'showLoginForm'])
        ->name('login');
    Route::middleware('throttle:4,1')->group(function () {
        Route::post('/auth/verify-phone', [FirebaseAuthController::class, 'verifyPhoneNumber']);
        Route::post('/auth/verify-token', [FirebaseAuthController::class, 'verifyToken']);
    });
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [FirebaseAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

