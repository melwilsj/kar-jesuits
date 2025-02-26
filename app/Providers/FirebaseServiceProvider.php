<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FirebaseAuthService;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FirebaseAuthService::class, function ($app) {
            return new FirebaseAuthService();
        });
    }

    public function boot()
    {
        //
    }
} 