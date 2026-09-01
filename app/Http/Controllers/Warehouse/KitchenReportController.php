<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;

class KitchenReportController extends Controller
{
    public function salesRanking()
    {
        // Simple logic for UI building. In production, this would join with the actual 'sales' and 'sale_items' tables to count actual revenue and quantity.
        // For now, we will query MenuItems and mock sales data for the report view.
        
        $menuItems = MenuItem::with('menuCategory')->get();
        
        // Mock sales logic for the UI since POS side isn't fully integrated into this table yet.
        $rankedItems = $menuItems->map(function($item) {
            $qty = rand(10, 500);
            return (object) [
                'name' => $item->name,
                'menuCategory' => (object)['name' => $item->menuCategory ? $item->menuCategory->name : 'N/A'],
                'price' => $item->price,
                'total_quantity_sold' => $qty,
                'total_revenue' => $item->price * $qty
            ];
        })->sortByDesc('total_revenue')->values();

        return view('warehouse.kitchen.reports.sales-ranking', compact('rankedItems'));
    }
}
