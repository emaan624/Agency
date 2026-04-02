<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\WalletService::class);
        $this->app->singleton(\App\Services\OrderService::class);
        $this->app->singleton(\App\Services\NotificationService::class);
    }

    public function boot(): void
    {
        // Make Setting model available as a global class in Blade templates
        if (!class_exists('Setting')) {
            class_alias(Setting::class, 'Setting');
        }
    }
}
