<?php

namespace Tests\Concerns;

use App\Models\WarePermission;
use App\Models\WareRole;
use App\Models\WareUser;

trait MakesWarehouseUsers
{
    private static int $userSequence = 0;

    protected function makeUser(array $attributes = [], array $roles = [], array $permissions = []): WareUser
    {
        $n = ++self::$userSequence;

        $user = WareUser::create(array_merge([
            'name' => "Test User {$n}",
            'email' => "user{$n}@test.local",
            'emp_code' => 'T' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'password' => bcrypt('password'),
            'is_active' => true,
        ], $attributes));

        foreach ($roles as $roleName) {
            $role = WareRole::firstOrCreate(['name' => $roleName, 'guard_name' => 'warehouse']);
            $user->roles()->attach($role->id);
        }

        if ($permissions) {
            $role = WareRole::create(['name' => "Test Role {$n}", 'guard_name' => 'warehouse']);
            foreach ($permissions as $permissionName) {
                $permission = WarePermission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'warehouse']);
                $role->permissions()->attach($permission->id);
            }
            $user->roles()->attach($role->id);
        }

        return $user->refresh();
    }

    protected function superAdmin(array $attributes = []): WareUser
    {
        return $this->makeUser($attributes, ['Super Admin']);
    }

    protected function userWithPermissions(array $permissions, array $attributes = []): WareUser
    {
        return $this->makeUser($attributes, [], $permissions);
    }
}
