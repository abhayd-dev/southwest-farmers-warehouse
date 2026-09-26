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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\WareSetting;
use App\Models\WarePermission;
use App\Models\WareUser;
use App\Support\PermissionCatalog;

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
        // UTC timestamp -> Central (config app.display_timezone), for display
        // only; storage stays UTC. Client 9/11 list, item 8: Audit Log times
        // were 5 hours ahead.
        $displayTime = function () {
            return $this->copy()->setTimezone(config('app.display_timezone', 'UTC'));
        };
        \Illuminate\Support\Carbon::macro('displayTime', $displayTime);
        \Carbon\Carbon::macro('displayTime', $displayTime);
        \Carbon\CarbonImmutable::macro('displayTime', $displayTime);

        Paginator::useBootstrapFive();
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Local .env files here point at the shared Railway database, so
        // migrate:fresh / db:wipe / rollback must be refused for it even when
        // APP_ENV=local. Keyed off the *default connection's* host, so the
        // sqlite test database is unaffected.
        $defaultHost = (string) config('database.connections.' . config('database.default') . '.host');
        DB::prohibitDestructiveCommands($this->app->isProduction() || str_contains($defaultHost, 'rlwy.net'));

        View::composer('layouts.partials.sidebar', SidebarComposer::class);

        // A newly seeded permission must be usable immediately, not after the
        // catalog cache expires.
        WarePermission::saved(fn () => PermissionCatalog::flush());
        WarePermission::deleted(fn () => PermissionCatalog::flush());

        // Lets views and controllers use @can('view_products') / $user->can(...)
        // for the app's permission names. Super Admin passes everything (same as
        // hasPermission()); names that aren't real permissions fall through
        // untouched so this can't interfere with any future policy.
        Gate::before(function ($user, string $ability) {
            if (!$user instanceof WareUser) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            return PermissionCatalog::has($ability) ? $user->hasPermission($ability) : null;
        });

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
