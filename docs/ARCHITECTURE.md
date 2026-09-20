# Architecture notes

## Two apps, one database

`warehouse-pos` (this repo) and `southwest-farmers-store` share one Postgres database. Each repo keeps its
own copy of the Eloquent models, which is where most past bugs came from (a model naming a column the table
doesn't have, `SoftDeletes` on a table without `deleted_at`, a `NOT NULL` column that isn't fillable).

**Who creates which tables**

| Created by | Tables |
|---|---|
| warehouse-pos migrations | ~90 (everything warehouse, catalog, procurement, stock, kitchen, support, ...) |
| store migrations only | `about_page_settings`, `contact_page_settings`, `home_page_settings`, `legal_pages`, `newsletter_subscribers`, `quick_pos_settings`, `store_time_logs` |
| both (guarded, no-op if present) | `enquiries`, `menu_categories`, `menu_items`, `store_sessions` |

**Rules**

1. A new *shared* table gets a migration in warehouse-pos, wrapped in `if (Schema::hasTable(...)) return;`.
2. A column added to a shared table is added by a migration in exactly one repo, guarded with `hasColumn`.
3. Change a shared model in both repos, then run `php artisan schema:drift` in **both**. It exits non-zero on
   any model/table mismatch; `tests/Feature/SchemaDriftTest.php` runs it against a schema built from migrations.
4. Never move or rename a model class. Polymorphic tables (`ware_model_has_roles`, `store_model_has_roles`,
   activity logs) store the class name, so a namespace move silently orphans that data.

## Authorization

Permissions used to be enforced only by hiding sidebar links. `config/route_permissions.php` now maps every
authenticated route name to the permission(s) the sidebar uses for it, applied by `EnforceRoutePermission`.

`PERMISSION_ENFORCEMENT` = `off` | `log` (default) | `enforce`.

Rolling out enforcement:
1. Leave it on `log`. Requests that would be denied are logged ("permission would be denied") to stderr and
   `storage/logs/permissions.log`.
2. Run `php artisan permissions:audit` (add `--details`, or `--user=ID`) to see what each real user would lose.
3. Fix real gaps in the map (or grant the permission to the role in the Roles screen), then set
   `PERMISSION_ENFORCEMENT=enforce` in the Railway environment. Set it back to `log` to roll back instantly.

`tests/Feature/RoutePermissionTest.php` guards the map: every authenticated route needs a rule (or an `open`
entry), and the sidebar/dashboard must never link a page the rules would deny, for each permission on its own.
The list of permissions lives in `database/seeders/WarePermissionCatalogSeeder.php`.

In views use `@can('view_products')` / `auth()->user()->can('view_products')` (Super Admin always passes).

## Tests

`php artisan test` runs against in-memory SQLite only. `TestCase` refuses to start on any other connection and
`phpunit.xml` forces sqlite, so a test can never touch the shared database. Helpers: `MakesWarehouseUsers`
(`superAdmin()`, `userWithPermissions([...])`) and `InsertsMinimalRows` (insert into any table without
hand-listing every required column).

## Safety guards

- `migrate:fresh`, `db:wipe`, `migrate:rollback` are refused whenever the default connection is the Railway host.
- `/debug-logs` and `/clear-cache` require login **and** Super Admin.
