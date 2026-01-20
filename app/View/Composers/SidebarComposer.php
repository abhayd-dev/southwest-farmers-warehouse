<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\StockRequest;
use App\Models\StoreStock;
use App\Models\RecallRequest;

class SidebarComposer
{
    public function compose(View $view)
    {
        $pendingRequestsCount = StockRequest::where('status', 'pending')->count();

        $lowStockCount = StoreStock::where('quantity', '<', 10)->count();

        $pendingRecallCount = RecallRequest::where('status', 'pending_store_approval')->count();

        $view->with(compact('pendingRequestsCount', 'lowStockCount', 'pendingRecallCount'));
    }
}