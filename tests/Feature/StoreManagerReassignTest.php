<?php

namespace Tests\Feature;

use App\Models\StoreDetail;
use App\Models\StoreRole;
use App\Models\StoreUser;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client ticket 14: Stores -> Edit had no way to hand the manager position
 * to another person (the hint pointed at the unrelated warehouse staff page).
 */
class StoreManagerReassignTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private StoreDetail $store;
    private StoreUser $manager;
    private StoreUser $cashier;
    private StoreRole $managerRole;
    private StoreRole $cashierRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutForeignKeys();

        $this->managerRole = StoreRole::create(['name' => 'Super Admin', 'guard_name' => 'store_user']);
        $this->cashierRole = StoreRole::create(['name' => 'Cashier', 'guard_name' => 'store_user']);

        $this->store = StoreDetail::find($this->insertRow('store_details', [
            'store_name' => 'Bissonnet', 'email' => 'store2@test.local', 'is_active' => true, 'store_group_id' => null,
        ]));
        $this->manager = $this->storeUser('Old Manager', $this->managerRole);
        $this->cashier = $this->storeUser('New Manager', $this->cashierRole);
        $this->store->update(['store_user_id' => $this->manager->id]);
    }

    private function storeUser(string $name, StoreRole $role, array $attributes = []): StoreUser
    {
        $user = StoreUser::create($attributes + [
            'store_id' => $this->store->id,
            'store_role_id' => $role->id,
            'name' => $name,
            'email' => str($name)->slug() . '@test.local',
            'password' => 'password',
            'is_active' => true,
        ]);
        DB::table('store_model_has_roles')->insert(['role_id' => $role->id, 'model_type' => StoreUser::class, 'model_id' => $user->id]);

        return $user;
    }

    private function update(array $overrides = [])
    {
        return $this->actingAs($this->superAdmin(), 'warehouse')->put(route('warehouse.stores.update', $this->store->id), $overrides + [
            'store_name' => 'Bissonnet',
            'store_email' => 'store2@test.local',
            'store_phone' => '9786766455',
            'city' => 'Houston',
            'address' => '9801 Bissonnet St',
        ]);
    }

    private function pivotRoles(StoreUser $user): array
    {
        return DB::table('store_model_has_roles')->where('model_type', StoreUser::class)
            ->where('model_id', $user->id)->pluck('role_id')->sort()->values()->all();
    }

    public function test_edit_page_offers_the_store_staff_as_manager_choices(): void
    {
        $this->actingAs($this->superAdmin(), 'warehouse')
            ->get(route('warehouse.stores.edit', $this->store->id))
            ->assertOk()
            ->assertSee('Change Manager')
            ->assertSee('name="store_user_id"', false)
            ->assertSee('New Manager')
            ->assertDontSee('My Profile Update');
    }

    public function test_reassigning_moves_the_manager_position_and_role(): void
    {
        $this->update(['store_user_id' => $this->cashier->id])
            ->assertRedirect(route('warehouse.stores.index'))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'New Manager is now the store manager'));

        $this->assertSame($this->cashier->id, $this->store->fresh()->store_user_id);
        $this->assertSame($this->managerRole->id, $this->cashier->fresh()->store_role_id);
        $this->assertContains($this->managerRole->id, $this->pivotRoles($this->cashier));
        $this->assertTrue($this->cashier->fresh()->isStoreAdmin());

        // The previous manager keeps their account and role, but is no longer the manager.
        $this->assertNotNull($this->manager->fresh());
        $this->assertSame($this->managerRole->id, $this->manager->fresh()->store_role_id);
        $this->assertFalse($this->manager->fresh()->isStoreAdmin());
    }

    public function test_saving_without_changing_the_manager_leaves_roles_alone(): void
    {
        $this->update(['store_user_id' => $this->manager->id])->assertSessionHas('success', 'Store details updated.');

        $this->assertSame($this->manager->id, $this->store->fresh()->store_user_id);
        $this->assertSame([$this->cashierRole->id], $this->pivotRoles($this->cashier));
    }

    public function test_staff_of_another_store_or_inactive_staff_cannot_be_made_manager(): void
    {
        $inactive = $this->storeUser('Inactive Person', $this->cashierRole, ['is_active' => false]);
        $otherStore = StoreDetail::find($this->insertRow('store_details', ['store_name' => 'Katy', 'email' => 'katy@test.local', 'is_active' => true]));
        $outsider = $this->storeUser('Outsider', $this->cashierRole, ['store_id' => $otherStore->id]);

        foreach ([$inactive, $outsider] as $user) {
            $this->update(['store_user_id' => $user->id])->assertSessionHasErrors('store_user_id');
            $this->assertSame($this->manager->id, $this->store->fresh()->store_user_id);
        }
    }

    public function test_staff_added_from_the_warehouse_gets_the_role_the_store_app_checks(): void
    {
        $this->actingAs($this->superAdmin(), 'warehouse')
            ->post(route('warehouse.stores.staff.store', $this->store->id), [
                'name' => 'Added Staff',
                'email' => 'added@test.local',
                'password' => 'password123',
                'store_role_id' => $this->cashierRole->id,
            ])->assertSessionHas('success');

        $staff = StoreUser::where('email', 'added@test.local')->firstOrFail();
        $this->assertSame([$this->cashierRole->id], $this->pivotRoles($staff));
    }
}
