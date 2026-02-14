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

        try {
            if (Schema::hasTable('ware_settings')) {
                $settings = Cache::rememberForever('ware_settings', function () {
                    return WareSetting::pluck('value', 'key')->toArray();
                });

                View::share('settings', $settings);
            } else {
                View::share('settings', []);
            }
        } catch (\Exception $e) {
            View::share('settings', []);
        }
     
    }
}
