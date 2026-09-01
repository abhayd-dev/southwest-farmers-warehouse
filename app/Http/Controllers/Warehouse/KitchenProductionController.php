<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\KitchenProduction;
use App\Models\LeftoverLog;
use App\Models\MenuItem;
use App\Models\KitchenLocation;
use App\Models\WareUser;
use Illuminate\Http\Request;

class KitchenProductionController extends Controller
{
    public function index()
    {
        $productions = KitchenProduction::with(['menuItem', 'kitchenLocation'])
            ->orderByDesc('produced_at')
            ->get();
        return view('warehouse.kitchen.production.index', compact('productions'));
    }

    public function create()
    {
        $menuItems = MenuItem::all();
        $kitchenLocations = KitchenLocation::all();
        $staff = WareUser::all();
        return view('warehouse.kitchen.production.create', compact('menuItems', 'kitchenLocations', 'staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id'       => 'required|exists:menu_items,id',
            'kitchen_location_id'=> 'required|exists:kitchen_locations,id',
            'ware_user_id'       => 'nullable',
            'quantity_made'      => 'required|numeric|min:0.01',
            'quantity_unit'      => 'required|string',
            'yield_plates'       => 'nullable|numeric|min:0',
            'daily_target'       => 'nullable|integer|min:0',
            'produced_at'        => 'required|date',
            'notes'              => 'nullable|string',
        ]);

        KitchenProduction::create($validated);

        return redirect()->route('kitchen.production.index')
            ->with('success', 'Production log saved successfully!');
    }

    public function leftovers()
    {
        $logs = LeftoverLog::with(['menuItem', 'kitchenLocation'])
            ->orderByDesc('log_date')
            ->get();
        return view('warehouse.kitchen.production.leftovers', compact('logs'));
    }
}
