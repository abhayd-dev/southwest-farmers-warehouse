<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class KitchenAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $categories = MenuCategory::with('menuItems')->get();
        
        $query = MenuItem::with('menuCategory');

        if ($request->filled('category_id')) {
            $query->where('menu_category_id', $request->category_id);
        }

        if ($request->filled('catering_filter')) {
            if ($request->catering_filter === 'catering_only') {
                $query->where('is_catering_only', true);
            } elseif ($request->catering_filter === 'regular') {
                $query->where('is_catering_only', false);
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $menuItems = $query->orderBy('name')->paginate(25);
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return view('warehouse.kitchen.availability.index', compact('menuItems', 'categories', 'daysOfWeek'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'is_available_today'   => 'nullable|boolean',
            'is_catering_only'     => 'nullable|boolean',
            'advance_notice_days'  => 'nullable|integer|min:0|max:90',
            'rush_fee_percentage'  => 'nullable|numeric|min:0|max:100',
            'available_days'       => 'nullable|array',
            'available_days.*'     => 'string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
        ]);

        $menuItem->update([
            'is_available_today'   => $request->has('is_available_today') ? (bool)$request->is_available_today : false,
            'is_catering_only'     => $request->has('is_catering_only') ? (bool)$request->is_catering_only : false,
            'advance_notice_days'  => $request->input('advance_notice_days', 7),
            'rush_fee_percentage'  => $request->input('rush_fee_percentage', 0),
            'available_days'       => $request->input('available_days', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Updated availability for {$menuItem->name}."
            ]);
        }

        return redirect()->back()->with('success', "Updated availability parameters for {$menuItem->name}.");
    }

    public function toggleToday(Request $request, MenuItem $menuItem)
    {
        $menuItem->is_available_today = !$menuItem->is_available_today;
        $menuItem->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_available_today' => $menuItem->is_available_today,
                'message' => "{$menuItem->name} is now " . ($menuItem->is_available_today ? 'Available Today' : 'Unavailable Today') . "."
            ]);
        }

        return redirect()->back()->with('success', "{$menuItem->name} availability status updated.");
    }
}
