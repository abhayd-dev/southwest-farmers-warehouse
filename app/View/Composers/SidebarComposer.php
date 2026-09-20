<?php

namespace App\View\Composers;

use App\Models\Enquiry;
use App\Models\RecallRequest;
use App\Models\StockRequest;
use App\Models\StorePurchaseOrder;
use App\Models\StoreStock;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidebarComposer
{
    // Sidebar badges are global counts, so they're cached briefly instead of
    // running six COUNT queries on every single page render.
    private const CACHE_SECONDS = 20;

    public function compose(View $view)
    {
        $counts = Cache::remember('sidebar.badge_counts', self::CACHE_SECONDS, fn () => [
            'pendingRequestsCount' => StockRequest::where('status', 'pending')->count(),
            'lowStockCount' => StoreStock::where('quantity', '<', 10)->count(),
            'pendingRecallCount' => RecallRequest::where('status', 'pending_store_approval')->count(),
            'pendingStorePOs' => StorePurchaseOrder::where('status', 'pending')->count(),
            'openTickets' => SupportTicket::where('status', 'open')->count(),
            'escalatedEnquiries' => Enquiry::whereIn('status', ['escalated_warehouse', 'escalated_admin'])->count(),
        ]);

        $view->with($counts);
    }
}
