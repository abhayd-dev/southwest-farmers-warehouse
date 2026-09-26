<?php

namespace Tests\Feature;

use App\Models\StoreDetail;
use App\Models\StoreGroup;
use App\Models\StoreUser;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client PDF 9/22, Store item 6: stores are grouped on the Warehouse side so
 * a group can be assigned to a Regional Manager on the Store side.
 */
class StoreGroupTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    private function store(string $name): StoreDetail
    {
        $this->withoutForeignKeys();

        return StoreDetail::find($this->insertRow('store_details', ['store_name' => $name, 'is_active' => true, 'store_group_id' => null]));
    }

    public function test_groups_page_lists_groups_and_their_stores(): void
    {
        $group = StoreGroup::create(['name' => 'Houston Region']);
        $this->store('Bissonnet')->update(['store_group_id' => $group->id]);

        $this->actingAs($this->superAdmin(), 'warehouse')
            ->get(route('warehouse.stores.groups.index'))
            ->assertOk()
            ->assertSee('Houston Region')
            ->assertSee('Bissonnet');
    }

    public function test_creating_a_group_assigns_the_ticked_stores(): void
    {
        $a = $this->store('Bissonnet');
        $b = $this->store('Katy');
        $c = $this->store('Dallas');

        $this->actingAs($this->superAdmin(), 'warehouse')
            ->post(route('warehouse.stores.groups.store'), ['name' => 'Houston Region', 'store_ids' => [$a->id, $b->id]])
            ->assertRedirect(route('warehouse.stores.groups.index'));

        $group = StoreGroup::where('name', 'Houston Region')->firstOrFail();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $group->stores()->pluck('id')->all());
        $this->assertNull($c->fresh()->store_group_id);
    }

    public function test_editing_a_group_moves_stores_in_and_out(): void
    {
        $houston = StoreGroup::create(['name' => 'Houston Region']);
        $dallasGroup = StoreGroup::create(['name' => 'Dallas Region']);
        $a = $this->store('Bissonnet');
        $b = $this->store('Katy');
        $a->update(['store_group_id' => $houston->id]);
        $b->update(['store_group_id' => $dallasGroup->id]);

        // Untick Bissonnet, tick Katy (moves it out of Dallas Region).
        $this->actingAs($this->superAdmin(), 'warehouse')
            ->put(route('warehouse.stores.groups.update', $houston), ['name' => 'Houston Region', 'store_ids' => [$b->id]]);

        $this->assertNull($a->fresh()->store_group_id);
        $this->assertSame($houston->id, $b->fresh()->store_group_id);
    }

    public function test_deleting_a_group_keeps_its_stores_and_staff(): void
    {
        $group = StoreGroup::create(['name' => 'Houston Region']);
        $store = $this->store('Bissonnet');
        $store->update(['store_group_id' => $group->id]);
        $rm = StoreUser::find($this->insertRow('store_users', ['name' => 'Collins', 'email' => 'rm@t.local', 'store_id' => $store->id, 'store_group_id' => $group->id]));

        $this->actingAs($this->superAdmin(), 'warehouse')
            ->delete(route('warehouse.stores.groups.destroy', $group));

        $this->assertModelMissing($group);
        $this->assertNull($store->fresh()->store_group_id);
        $this->assertNull($rm->fresh()->store_group_id);
    }

    public function test_group_names_are_unique(): void
    {
        StoreGroup::create(['name' => 'Houston Region']);

        $this->actingAs($this->superAdmin(), 'warehouse')
            ->post(route('warehouse.stores.groups.store'), ['name' => 'Houston Region'])
            ->assertSessionHasErrors('name');
    }

    public function test_the_groups_url_is_not_swallowed_by_the_stores_resource(): void
    {
        $this->assertSame('warehouse.stores.groups.index', app('router')->getRoutes()->match(
            \Illuminate\Http\Request::create('/warehouse/stores/groups')
        )->getName());
    }

    public function test_all_stores_list_and_store_edit_show_the_group(): void
    {
        $group = StoreGroup::create(['name' => 'Houston Region']);
        $store = $this->store('Bissonnet');
        $store->update(['store_group_id' => $group->id]);
        $admin = $this->superAdmin();

        $this->actingAs($admin, 'warehouse')->get(route('warehouse.stores.index'))
            ->assertOk()->assertSee('Store Groups')->assertSee('Houston Region');

        $this->actingAs($admin, 'warehouse')->get(route('warehouse.stores.index', ['store_group_id' => $group->id]))
            ->assertOk()->assertSee('Bissonnet');

        $this->actingAs($admin, 'warehouse')->get(route('warehouse.stores.edit', $store->id))
            ->assertOk()->assertSee('name="store_group_id"', false)->assertSee('Houston Region');
    }
}
