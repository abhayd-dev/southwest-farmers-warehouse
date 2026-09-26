<?php

namespace App\Http\Controllers\Warehouse\Stores;

use App\Http\Controllers\Controller;
use App\Models\StoreDetail;
use App\Models\StoreGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Store Groups (client PDF 9/22, Store item 6). Groups are managed here and
 * stores are placed in them; on the Store side a group can then be assigned
 * to a staff member (e.g. a Regional Manager) to give them every store in it.
 */
class StoreGroupController extends Controller
{
    public function index()
    {
        $groups = StoreGroup::with(['stores' => fn ($q) => $q->orderBy('store_name')])
            ->withCount('staff')
            ->orderBy('name')
            ->get();
        $stores = StoreDetail::orderBy('store_name')->get(['id', 'store_name', 'store_code', 'city', 'store_group_id']);

        return view('warehouse.stores.groups', compact('groups', 'stores'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $group = StoreGroup::create($data);
        $this->assignStores($group, $request->input('store_ids', []));

        return redirect()->route('warehouse.stores.groups.index')->with('success', "Store group \"{$group->name}\" created.");
    }

    public function update(Request $request, StoreGroup $group)
    {
        $group->update($this->validated($request, $group));
        $this->assignStores($group, $request->input('store_ids', []));

        return redirect()->route('warehouse.stores.groups.index')->with('success', "Store group \"{$group->name}\" updated.");
    }

    public function destroy(StoreGroup $group)
    {
        // Stores and staff are released (store_group_id -> null), not deleted.
        StoreDetail::where('store_group_id', $group->id)->update(['store_group_id' => null]);
        \App\Models\StoreUser::where('store_group_id', $group->id)->update(['store_group_id' => null]);
        $name = $group->name;
        $group->delete();

        return redirect()->route('warehouse.stores.groups.index')->with('success', "Store group \"{$name}\" deleted.");
    }

    private function validated(Request $request, ?StoreGroup $group = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('store_groups', 'name')->ignore($group?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', 'exists:store_details,id'],
        ]);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    /** A store belongs to at most one group: ticking it here moves it into this one. */
    private function assignStores(StoreGroup $group, array $storeIds): void
    {
        $storeIds = array_map('intval', $storeIds);

        StoreDetail::where('store_group_id', $group->id)
            ->whereNotIn('id', $storeIds ?: [0])
            ->update(['store_group_id' => null]);

        if ($storeIds) {
            StoreDetail::whereIn('id', $storeIds)->update(['store_group_id' => $group->id]);
        }
    }
}
