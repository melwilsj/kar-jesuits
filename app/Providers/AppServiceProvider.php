<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ProvinceTransfer;
use App\Observers\ProvinceTransferObserver;

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
        ProvinceTransfer::observe(ProvinceTransferObserver::class);
    }
}
