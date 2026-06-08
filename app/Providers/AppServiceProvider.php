<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Mendaftarkan 2 Detektif History Aset
        \App\Models\Asset::observe(\App\Observers\AssetObserver::class);
        \App\Models\AssetValue::observe(\App\Observers\AssetValueObserver::class);
    }
}