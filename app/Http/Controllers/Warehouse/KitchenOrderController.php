<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;

class KitchenOrderController extends Controller
{
    public function index()
    {
        // Fetch active orders (not completed, not cancelled)
        $orders = Sale::with('items.product')
            ->whereNotIn('kitchen_status', ['Completed', 'Cancelled'])
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Group orders by their current status for Kanban columns
        $kanbanData = [
            'New' => $orders->where('kitchen_status', 'New')->values(),
            'Accepted' => $orders->where('kitchen_status', 'Accepted')->values(),
            'Preparing' => $orders->where('kitchen_status', 'Preparing')->values(),
            'Ready' => $orders->where('kitchen_status', 'Ready')->values(),
        ];

        return view('warehouse.kitchen.kds', compact('kanbanData'));
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'status' => 'required|in:Accepted,Preparing,Ready,Completed,Cancelled'
        ]);

        $sale->update(['kitchen_status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated to ' . $request->status
        ]);
    }
}
