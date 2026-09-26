<?php

namespace App\Http\Controllers\Warehouse\Helpdesk;

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
use Illuminate\Support\Facades\Log;
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
                $path = $file->store('support-attachments', 'r2');
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

        // Store-facing reply (not an internal note)
        if (!$msg->is_internal) {
            // Auto-update status if open
            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }

            // Best-effort email: a mail outage must not turn a saved reply into
            // a 500 (QA: "shows status 500 error when I try to send a message").
            try {
                $recipient = $ticket->createdBy->email ?? $ticket->store->email;
                Mail::to($recipient)->send(new SupportTicketReplied($ticket, $msg));
            } catch (\Throwable $e) {
                Log::error('Failed to send SupportTicketReplied email for ticket #' . $ticket->id . ': ' . $e->getMessage());
            }

            $this->notifyStore($ticket, 'Ticket Reply', "New reply from the warehouse on ticket #{$ticket->ticket_number}.", 'success');
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    // 4. Change Status / Assign
    public function update(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        if ($request->has('status') && $request->status !== $ticket->status) {
            $oldStatus = $ticket->status;
            $ticket->update([
                'status' => $request->status,
                'resolved_at' => $request->status === 'resolved' ? now() : $ticket->resolved_at,
                'closed_at' => $request->status === 'closed' ? now() : null,
            ]);

            // Log Status Change
            SupportStatusLog::create([
                'ticket_id' => $ticket->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'changed_by_id' => Auth::id(),
                'changed_by_type' => get_class(Auth::user()),
            ]);

            // Notify Store (best-effort: a mail outage must not block the status/assignment save)
            try {
                $recipient = $ticket->createdBy->email ?? $ticket->store->email;
                Mail::to($recipient)->send(new SupportTicketStatusChanged($ticket));
            } catch (\Throwable $e) {
                Log::error('Failed to send SupportTicketStatusChanged email for ticket #' . $ticket->id . ': ' . $e->getMessage());
            }

            $this->notifyStore($ticket, 'Ticket Updated', "Your ticket #{$ticket->ticket_number} is now: " . ucfirst(str_replace('_', ' ', $ticket->status)) . '.');
        }

        if ($request->has('assigned_to_id')) {
            $ticket->update(['assigned_to_id' => $request->assigned_to_id ?: null]);
        }

        return back()->with('success', 'Ticket updated.');
    }

    /**
     * In-app notice to the store user who raised the ticket (their bell on the
     * Store side). The old code checked a non-existent $ticket->created_by, so
     * stores were never notified in-app of warehouse replies or status changes.
     */
    private function notifyStore(SupportTicket $ticket, string $title, string $message, string $type = 'info'): void
    {
        if ($ticket->created_by_type !== \App\Models\StoreUser::class || ! $ticket->created_by_id) {
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::table('store_notifications')->insert([
                'user_id' => $ticket->created_by_id,
                'store_id' => $ticket->store_id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'url' => '/store/support/' . $ticket->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to notify store about ticket #' . $ticket->id . ': ' . $e->getMessage());
        }
    }
}
