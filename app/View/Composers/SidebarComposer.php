<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\StockRequest;
use App\Models\StoreStock;

class SidebarComposer
{
    public function compose(View $view)
    {
        $pendingRequestsCount = StockRequest::where('status', 'pending')->count();

        $lowStockCount = StoreStock::where('quantity', '<', 10)->count();

        $view->with('pendingRequestsCount', $pendingRequestsCount);
        $view->with('lowStockCount', $lowStockCount);
    }
}
