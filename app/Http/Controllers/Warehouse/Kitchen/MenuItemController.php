<?php

namespace App\Http\Controllers\Warehouse\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $items = MenuItem::with('category')->get();
        return view('warehouse.kitchen.menu-items', compact('items'));
    }
}
