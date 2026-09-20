<?php

namespace App\Http\Controllers\Warehouse\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\RecipeStep;
use Illuminate\Http\Request;

class CookbookController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with('menuItem')->get();
        return view('warehouse.kitchen.cookbook.index', compact('recipes'));
    }

    public function create()
    {
        $menuItems = MenuItem::all();
        $products = Product::all();
        return view('warehouse.kitchen.cookbook.create', compact('menuItems', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prep_time_minutes' => 'nullable|integer',
            'cook_time_minutes' => 'nullable|integer',
        ]);

        Recipe::create($validated);

        return redirect()->route('kitchen.cookbook.index')->with('success', 'Recipe created successfully!');
    }

    public function show($id)
    {
        $recipe = Recipe::with(['menuItem', 'steps'])->findOrFail($id);
        return view('warehouse.kitchen.cookbook.show', compact('recipe'));
    }

    public function edit($id)
    {
        $recipe = Recipe::findOrFail($id);
        $menuItems = MenuItem::all();
        return view('warehouse.kitchen.cookbook.edit', compact('recipe', 'menuItems'));
    }

    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prep_time_minutes' => 'nullable|integer',
            'cook_time_minutes' => 'nullable|integer',
        ]);

        $recipe->update($validated);

        return redirect()->route('kitchen.cookbook.index')->with('success', 'Recipe updated successfully!');
    }

    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();
        return redirect()->route('kitchen.cookbook.index')->with('success', 'Recipe deleted successfully!');
    }
}
