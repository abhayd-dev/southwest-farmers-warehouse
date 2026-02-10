<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\WareNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // 1. "View All" Page
    public function index()
    {
        $notifications = WareNotification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('warehouse.notifications.index', compact('notifications'));
    }

    // 2. AJAX: Fetch Topbar Data (Count + List)
    public function fetchLatest()
    {
        $user = Auth::user();
        
        $count = WareNotification::where('user_id', $user->id)->unread()->count();
        
        $list = WareNotification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => \Str::limit($n->message, 40),
                    'type' => $n->type, // success, danger, warning
                    'url' => $n->url ?? '#',
                    'time' => $n->created_at->diffForHumans(),
                    'read' => !is_null($n->read_at)
                ];
            });

        return response()->json(['count' => $count, 'notifications' => $list]);
    }

    // 3. Mark Single as Read
    public function markRead($id)
    {
        $notif = WareNotification::where('user_id', Auth::id())->findOrFail($id);
        $notif->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    // 4. Mark All as Read
    public function markAllRead()
    {
        WareNotification::where('user_id', Auth::id())->unread()->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    // 5. Delete Notification
    public function destroy($id)
    {
        $notif = WareNotification::where('user_id', Auth::id())->findOrFail($id);
        $notif->delete();

        return back()->with('success', 'Notification deleted.');
    }
}