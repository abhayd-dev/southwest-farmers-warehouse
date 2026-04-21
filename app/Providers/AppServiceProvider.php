<?php

namespace App\Providers;

use App\View\Composers\SidebarComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\WareSetting;

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
        Paginator::useBootstrapFive();
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.partials.sidebar', SidebarComposer::class);

        // Default critical settings to prevent view crashes
        $defaults = [
            'login_logo' => 'settings/default_logo.png',
            'main_logo'  => 'settings/default_logo.png',
            'favicon'    => 'settings/default_favicon.png',
            'app_name'   => 'Warehouse POS',
        ];

        try {
            if (Schema::hasTable('ware_settings')) {
                // Try cache first
                try {
                    $settings = Cache::get('ware_settings');
                } catch (\Exception $e) {
                    $settings = null;
                }

                if (!$settings) {
                    $settings = WareSetting::pluck('value', 'key')->toArray();
                    // Attempt to cache, but don't fail if cache storage is broken
                    try {
                        Cache::forever('ware_settings', $settings);
                    } catch (\Exception $e) {
                        // Cache storage failed (e.g. SQL error in cache table)
                    }
                }

                // Merge retrieved settings with defaults
                $settings = array_merge($defaults, $settings);
                View::share('settings', $settings);
            } else {
                View::share('settings', $defaults);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to load warehouse settings: " . $e->getMessage());
            View::share('settings', $defaults);
        }
     
    }
}
