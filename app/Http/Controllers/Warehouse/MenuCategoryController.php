<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::all();
        return view('warehouse.kitchen.menu-categories', compact('categories'));
    }
}
