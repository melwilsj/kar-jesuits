<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ProvinceTransfer;
use App\Observers\ProvinceTransferObserver;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        ProvinceTransfer::observe(ProvinceTransferObserver::class);
    }
}
