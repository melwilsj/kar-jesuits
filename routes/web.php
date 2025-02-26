<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Livewire\Volt\Volt;
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Livewire\Admin\Dashboard as AdminDashboard;

// Public routes
Route::get('/', App\Livewire\Welcome::class)->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Guest routes
Route::middleware('guest')->group(function () {
    // Firebase User Authentication
    Route::get('/login', [FirebaseAuthController::class, 'showLoginForm'])->name('login');
    Route::middleware('throttle.auth')->group(function () {
        Route::post('/auth/verify-phone', [FirebaseAuthController::class, 'verifyPhone']);
        Route::post('/auth/verify-token', [FirebaseAuthController::class, 'verifyToken']);
    });
    
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminLoginController::class, 'login']);
        Route::post('/verify-firebase-token', [AdminLoginController::class, 'verifyFirebaseToken']);
    });
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [FirebaseAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Protected admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
    
    // User management routes
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', function() { return view('admin.users.index'); })->name('index');
        Route::get('/create', function() { return view('admin.users.create'); })->name('create');
        Route::get('/{user}/edit', function($user) { return view('admin.users.edit', ['user' => $user]); })->name('edit');
    });
});
