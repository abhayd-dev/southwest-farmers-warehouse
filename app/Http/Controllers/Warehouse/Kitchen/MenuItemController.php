<?php

namespace App\Http\Controllers\Warehouse\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $items = MenuItem::with('menuCategory')->get(); // the view reads menuCategory (there is no 'category' relation)
        return view('warehouse.kitchen.menu-items', compact('items'));
    }
}
