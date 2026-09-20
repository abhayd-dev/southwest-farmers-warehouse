<?php

namespace Tests\Feature;

use App\Support\RoutePermissions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

class RoutePermissionTest extends TestCase
{
    use MakesWarehouseUsers;

    private function mode(string $mode): void
    {
        config(['route_permissions.mode' => $mode]);
    }

    // ---- enforcement -------------------------------------------------------

    public function test_enforce_mode_blocks_a_user_who_lacks_the_route_permission(): void
    {
        $this->mode('enforce');
        $user = $this->userWithPermissions(['view_products']);

        $this->actingAs($user)->get(route('warehouse.vendors.index'))->assertForbidden();
        $this->actingAs($user)->get(route('warehouse.finance.index'))->assertForbidden();
        $this->actingAs($user)->get(route('warehouse.stock-control.valuation'))->assertForbidden();
    }

    public function test_enforce_mode_allows_a_user_who_has_the_permission(): void
    {
        $this->mode('enforce');

        $this->actingAs($this->userWithPermissions(['view_vendors']))
            ->get(route('warehouse.vendors.index'))->assertOk();
    }

    public function test_super_admin_passes_everywhere(): void
    {
        $this->mode('enforce');
        $admin = $this->superAdmin();

        foreach (['warehouse.vendors.index', 'warehouse.finance.index', 'warehouse.stock-control.valuation', 'warehouse.roles.index'] as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }
    }

    public function test_any_of_permissions_are_honoured(): void
    {
        $this->mode('enforce');

        $this->actingAs($this->userWithPermissions(['create_products']))->get(route('warehouse.products.create'))->assertOk();
        $this->actingAs($this->userWithPermissions(['manage_products']))->get(route('warehouse.products.create'))->assertOk();
        $this->actingAs($this->userWithPermissions(['view_products']))->get(route('warehouse.products.create'))->assertForbidden();
    }

    public function test_open_routes_stay_reachable_with_no_permissions_at_all(): void
    {
        $this->mode('enforce');
        $nobody = $this->makeUser();

        foreach (['dashboard', 'profile.edit', 'warehouse.notifications.index'] as $name) {
            $this->actingAs($nobody)->get(route($name))->assertOk();
        }
    }

    public function test_audit_logs_can_be_opened_with_the_permission_the_sidebar_actually_uses(): void
    {
        // The route used to require "view_activity_logs", a permission that
        // doesn't exist, so only Super Admin could ever open a link the
        // sidebar showed to anyone holding "view_audit_logs".
        $this->mode('enforce');

        $this->actingAs($this->userWithPermissions(['view_audit_logs']))
            ->get(route('warehouse.activity-logs.index'))->assertOk();
    }

    // ---- modes -------------------------------------------------------------

    public function test_log_mode_lets_the_request_through_and_records_it(): void
    {
        $this->mode('log');
        $channel = \Mockery::spy();
        Log::partialMock()->shouldReceive('channel')->with('permissions')->andReturn($channel);

        $this->actingAs($this->userWithPermissions(['view_products']))
            ->get(route('warehouse.vendors.index'))->assertOk();

        $channel->shouldHaveReceived('warning')->once()->withArgs(
            fn ($message, $context) => $context['route'] === 'warehouse.vendors.index'
                && $context['requires_any_of'] === ['view_vendors']
        );
    }

    public function test_log_mode_is_the_default(): void
    {
        $this->assertSame('log', RoutePermissions::mode());
    }

    public function test_off_mode_does_nothing(): void
    {
        $this->mode('off');
        $channel = \Mockery::spy();
        Log::partialMock()->shouldReceive('channel')->with('permissions')->andReturn($channel);

        $this->actingAs($this->userWithPermissions(['view_products']))
            ->get(route('warehouse.vendors.index'))->assertOk();

        $channel->shouldNotHaveReceived('warning');
    }

    public function test_an_unknown_mode_falls_back_to_log_rather_than_locking_everyone_out(): void
    {
        $this->mode('banana');

        $this->assertSame('log', RoutePermissions::mode());
        $this->actingAs($this->userWithPermissions(['view_products']))
            ->get(route('warehouse.vendors.index'))->assertOk();
    }

    // ---- the map itself ----------------------------------------------------

    public function test_every_authenticated_route_is_either_mapped_or_explicitly_open(): void
    {
        $unmapped = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if ($name && in_array('route_permission', $route->gatherMiddleware(), true) && RoutePermissions::isUnmapped($name)) {
                $unmapped[] = $name;
            }
        }

        $this->assertSame([], $unmapped, "New routes need a rule in config/route_permissions.php (or an entry under 'open'):\n" . implode("\n", $unmapped));
    }

    public function test_every_pattern_in_the_map_matches_at_least_one_real_route(): void
    {
        $names = collect(Route::getRoutes()->getRoutesByName())->keys();
        $dead = [];

        foreach (array_merge(array_keys(config('route_permissions.map')), config('route_permissions.open')) as $pattern) {
            if ($names->doesntContain(fn ($name) => Str::is($pattern, $name))) {
                $dead[] = $pattern;
            }
        }

        $this->assertSame([], $dead, 'Patterns matching no route (typo or removed route): ' . implode(', ', $dead));
    }

    public function test_every_permission_in_the_map_is_created_by_a_seeder(): void
    {
        $seedSource = collect(glob(database_path('seeders/*.php')))->map(fn ($f) => file_get_contents($f))->implode("\n");
        $missing = collect(config('route_permissions.map'))->flatten()->unique()
            ->reject(fn ($permission) => str_contains($seedSource, "'{$permission}'") || str_contains($seedSource, "\"{$permission}\""))
            ->values()->all();

        $this->assertSame([], $missing, 'Permissions used in the map but seeded nowhere: ' . implode(', ', $missing));
    }

    // ---- gate bridge / views ----------------------------------------------

    public function test_can_mirrors_has_permission_for_real_permissions(): void
    {
        $viewer = $this->userWithPermissions(['view_products']);

        $this->assertTrue($viewer->can('view_products'));
        $this->assertFalse($viewer->can('view_vendors'));
        $this->assertTrue($this->superAdmin()->can('view_vendors'));
        $this->assertFalse($this->makeUser()->can('view_products'));
    }

    public function test_sidebar_only_shows_sections_the_user_may_open(): void
    {
        $vendorsOnly = $this->actingAs($this->userWithPermissions(['view_vendors']))->get(route('dashboard'));
        $vendorsOnly->assertOk()->assertSee(route('warehouse.vendors.index'), false)
            ->assertDontSee(route('warehouse.finance.index'), false)
            ->assertDontSee(route('warehouse.stock-control.valuation'), false);

        $finance = $this->actingAs($this->userWithPermissions(['view_financial_reports']))->get(route('dashboard'));
        $finance->assertSee(route('warehouse.finance.index'), false)
            ->assertDontSee(route('warehouse.vendors.index'), false);
    }

    public function test_the_sidebar_never_links_to_a_page_the_route_rules_would_deny(): void
    {
        // The invariant that makes enforcement safe: for every permission a user
        // could hold, each link the menu shows must be reachable.
        $inconsistencies = [];

        foreach (\Database\Seeders\WarePermissionCatalogSeeder::PERMISSIONS as $permission) {
            $user = $this->userWithPermissions([$permission]);
            $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

            preg_match_all('/<a[^>]+href="([^"#]+)"/', $html, $matches);

            foreach (array_unique($matches[1]) as $href) {
                if (!str_starts_with($href, url('/'))) {
                    continue;
                }

                try {
                    $route = Route::getRoutes()->match(\Illuminate\Http\Request::create($href, 'GET'));
                } catch (\Throwable) {
                    continue;
                }

                $required = $route->getName() ? RoutePermissions::requiredFor($route->getName()) : null;

                if ($required !== null && !RoutePermissions::allows($user, $required)) {
                    $inconsistencies[] = "{$permission}: sidebar shows {$route->getName()} but it needs " . implode(' or ', $required);
                }
            }
        }

        $this->assertSame([], array_values(array_unique($inconsistencies)), implode("\n", array_unique($inconsistencies)));
    }

    public function test_a_newly_created_permission_is_usable_immediately(): void
    {
        $user = $this->makeUser();
        $this->assertFalse($user->can('brand_new_permission'));

        $user = $this->userWithPermissions(['brand_new_permission']);
        $this->assertTrue($user->can('brand_new_permission'), 'the catalog cache must be flushed when a permission is created');
    }
}
