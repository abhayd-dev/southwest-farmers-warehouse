<!DOCTYPE html>
<html>
<head>
    <title>New Support Ticket</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #fff; padding: 20px; border-radius: 5px; max-width: 600px; margin: auto;">
        <h2 style="color: #333;">New Support Ticket Created</h2>
        <p>Hello Support Team,</p>
        
        <p>A new ticket has been raised by <strong>{{ $ticket->store->store_name }}</strong>.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Ticket ID:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $ticket->ticket_number }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Subject:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $ticket->subject }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Priority:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; color: red;">{{ ucfirst($ticket->priority) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>SLA Due:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $ticket->sla_due_at->format('d M Y, h:i A') }}</td>
            </tr>
        </table>

        <br>
        <a href="{{ route('warehouse.support.show', $ticket->id) }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Ticket</a>
        
        <p style="margin-top: 20px; color: #777; font-size: 12px;">Warehouse POS System Automation</p>
    </div>
</body>
</html>