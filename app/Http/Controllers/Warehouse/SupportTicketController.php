<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\SupportStatusLog;
use App\Models\WareUser;
use App\Mail\SupportTicketReplied;
use App\Mail\SupportTicketStatusChanged;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    // 1. Dashboard & List
    public function index(Request $request)
    {
        set_time_limit(300); // Extend execution time for large datasets
        if (!Auth::user()->hasPermission('view_all_tickets')) {
            abort(403);
        }

        $query = SupportTicket::with(['store', 'assignedTo', 'createdBy'])->latest();

        // Filters
        if ($request->status) $query->where('status', $request->status);
        if ($request->priority) $query->where('priority', $request->priority);
        if ($request->store_id) $query->where('store_id', $request->store_id);

        $tickets = $query->paginate(15);

        // Dashboard Metrics
        $metrics = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'overdue' => SupportTicket::where('status', '!=', 'closed')->where('sla_due_at', '<', now())->count(),
            'critical' => SupportTicket::where('priority', 'critical')->where('status', '!=', 'closed')->count(),
        ];

        return view('warehouse.support.index', compact('tickets', 'metrics'));
    }

    // 2. Show Ticket
    public function show($id)
    {
        set_time_limit(300); // Extend execution time for large tickets
        $ticket = SupportTicket::with(['messages.sender', 'messages.attachments', 'attachments'])->findOrFail($id);
        $staff = WareUser::where('is_active', true)->get(); // For assignment dropdown

        return view('warehouse.support.show', compact('ticket', 'staff'));
    }

    // 3. Reply / Add Note
    public function reply(Request $request, $id)
    {
        $request->validate(['message' => 'required']);
        $ticket = SupportTicket::findOrFail($id);

        // Create Message
        $msg = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => get_class(Auth::user()),
            'message' => $request->message,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        // Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-attachments', 'public');
                $msg->attachments()->create([
                    'ticket_id' => $ticket->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->extension(),
                    'uploaded_by_id' => Auth::id(),
                    'uploaded_by_type' => get_class(Auth::user()),
                ]);
            }
        }

        // Send Email (Only if not internal note)
        if (!$msg->is_internal) {
            // Assuming store user has email
            $recipient = $ticket->createdBy->email ?? $ticket->store->email;
            Mail::to($recipient)->send(new SupportTicketReplied($ticket, $msg));

            // Auto-update status if open
            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }
        }

        if (!$request->has('is_internal')) {
            // If replying to a user's ticket, notify them
            if ($ticket->created_by && $ticket->created_by != auth()->id()) {
                NotificationService::send(
                    $ticket->created_by,
                    'Ticket Reply',
                    "New reply on ticket #{$ticket->ticket_number}",
                    'success',
                    route('warehouse.support.show', $ticket->id)
                );
            }
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    // 4. Change Status / Assign
    public function update(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($request->has('status')) {
            $oldStatus = $ticket->status;
            $ticket->update(['status' => $request->status]);

            // Log Status Change
            SupportStatusLog::create([
                'ticket_id' => $ticket->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'changed_by_id' => Auth::id(),
                'changed_by_type' => get_class(Auth::user()),
            ]);

            // Notify Store
            $recipient = $ticket->createdBy->email ?? $ticket->store->email;
            Mail::to($recipient)->send(new SupportTicketStatusChanged($ticket));

            if ($ticket->created_by && $ticket->created_by != auth()->id()) {
                NotificationService::send(
                    $ticket->created_by,
                    'Ticket Updated',
                    "Your ticket #{$ticket->ticket_number} status is now: " . ucfirst($ticket->status),
                    'info',
                    route('warehouse.support.show', $ticket->id)
                );
            }
        }

        if ($request->has('assigned_to_id')) {
            $ticket->update(['assigned_to_id' => $request->assigned_to_id]);
        }

        return back()->with('success', 'Ticket updated.');
    }
}
