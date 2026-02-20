<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index()
    {
        $markets = Market::latest()->paginate(10);
        return view('warehouse.markets.index', compact('markets'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Market::create($request->all());
        return back()->with('success', 'Market created');
    }

    public function update(Request $request, Market $market)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $market->update($request->all());
        return back()->with('success', 'Market updated');
    }

    public function changeStatus(Request $request)
    {
        $market = Market::findOrFail($request->id);
        $market->update(['is_active' => $request->status]);
        return response()->json(['message' => 'Status updated']);
    }
}
