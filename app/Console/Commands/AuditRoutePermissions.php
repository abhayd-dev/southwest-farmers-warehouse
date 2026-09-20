<?php

namespace App\Console\Commands;

use App\Models\WareUser;
use App\Support\PermissionCatalog;
use App\Support\RoutePermissions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * Read-only. Shows what switching PERMISSION_ENFORCEMENT to "enforce" would do
 * to each real user, so it can be reviewed before anyone is locked out of a page.
 */
class AuditRoutePermissions extends Command
{
    protected $signature = 'permissions:audit
                            {--user= : Only audit this user id}
                            {--details : List every route a user would lose, not just a summary}';

    protected $description = 'Show which routes each user would lose if permission enforcement were switched on';

    public function handle(): int
    {
        $routes = $this->guardedRoutes();

        $this->info('Mode: ' . RoutePermissions::mode() . '   (set PERMISSION_ENFORCEMENT=off|log|enforce)');
        $this->line('Authenticated named routes: ' . count($routes));

        $this->reportMapProblems($routes);

        $users = WareUser::where('is_active', true)->with('roles.permissions', 'permissions')
            ->when($this->option('user'), fn ($q, $id) => $q->where('id', $id))
            ->orderBy('name')->get();

        $rows = [];
        foreach ($users as $user) {
            if ($user->isSuperAdmin()) {
                $rows[] = [$user->id, $user->name, $this->roleNames($user), 'Super Admin', 0, '-'];
                continue;
            }

            $denied = [];
            foreach ($routes as $name => $required) {
                if (!RoutePermissions::allows($user, $required)) {
                    $denied[$name] = $required;
                }
            }

            $byPermission = [];
            foreach ($denied as $required) {
                $key = implode(' | ', $required);
                $byPermission[$key] = ($byPermission[$key] ?? 0) + 1;
            }
            arsort($byPermission);

            $summary = collect($byPermission)->map(fn ($n, $p) => "{$p} ({$n})")->take(4)->implode(', ');
            if (count($byPermission) > 4) {
                $summary .= ', +' . (count($byPermission) - 4) . ' more';
            }

            $rows[] = [$user->id, $user->name, $this->roleNames($user), count($denied) . ' routes', count($denied), $summary ?: '-'];

            if ($this->option('details') && $denied) {
                $this->newLine();
                $this->warn("{$user->name} would lose:");
                foreach ($denied as $name => $required) {
                    $this->line("   {$name}  (needs " . implode(' or ', $required) . ')');
                }
            }
        }

        $this->newLine();
        $this->table(['ID', 'User', 'Roles', 'Would lose', 'n', 'Missing permissions (route count)'], $rows);

        return self::SUCCESS;
    }

    /** @return array<string, string[]> route name => any-of permissions, for routes that need one */
    private function guardedRoutes(): array
    {
        $guarded = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (!$name || !in_array('route_permission', $route->gatherMiddleware(), true)) {
                continue;
            }
            if ($required = RoutePermissions::requiredFor($name)) {
                $guarded[$name] = $required;
            }
        }

        ksort($guarded);

        return $guarded;
    }

    private function reportMapProblems(array $guarded): void
    {
        $known = PermissionCatalog::names();

        $unknown = collect($guarded)->flatten()->unique()->reject(fn ($p) => in_array($p, $known, true))->values();
        if ($unknown->isNotEmpty()) {
            $this->error('Map references permissions that do not exist in ware_permissions: ' . $unknown->implode(', '));
        }

        $unmapped = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if ($name && in_array('route_permission', $route->gatherMiddleware(), true) && RoutePermissions::isUnmapped($name)) {
                $unmapped[] = $name;
            }
        }
        sort($unmapped);

        if ($unmapped) {
            $this->warn('Authenticated routes with NO permission rule (any logged-in user can open them):');
            foreach ($unmapped as $name) {
                $this->line('   ' . $name);
            }
        }
    }

    private function roleNames(WareUser $user): string
    {
        return $user->roles->pluck('name')->unique()->implode(', ') ?: '(none)';
    }
}
