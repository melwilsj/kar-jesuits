<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Livewire\Volt\Volt;
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;

// Public routes
Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Guest routes
Route::middleware('guest')->group(function () {
    // Firebase User Authentication
    Route::get('/login', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/verify-token', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'verifyToken']);
    Route::post('/auth/verify-phone', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'verifyPhoneNumber']);
    
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [App\Http\Controllers\Auth\AdminLoginController::class, 'login']);
        Route::post('/verify-firebase-token', [App\Http\Controllers\Auth\AdminLoginController::class, 'verifyFirebaseToken']);
    });
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Auth\FirebaseAuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Protected admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [App\Http\Controllers\Auth\AdminLoginController::class, 'logout'])->name('admin.logout');
    
    // User management routes
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', function() {
            return view('admin.users.index');
        })->name('index');
        
        Route::get('/create', function() {
            return view('admin.users.create');
        })->name('create');
        
        Route::get('/{user}/edit', function($user) {
            return view('admin.users.edit', ['user' => $user]);
        })->name('edit');
    });
});
