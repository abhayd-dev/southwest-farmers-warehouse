<!DOCTYPE html>
<html>
<head>
    <title>Ticket Status Updated</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #fff; padding: 20px; border-radius: 5px; max-width: 600px; margin: auto;">
        <h2 style="color: #333;">Ticket Status Updated</h2>
        
        <p>Hello,</p>
        <p>The status of your support ticket <strong>#{{ $ticket->ticket_number }}</strong> has been updated.</p>

        <div style="text-align: center; margin: 20px 0;">
            <span style="font-size: 18px; font-weight: bold; padding: 10px 20px; background-color: #e2e6ea; border-radius: 20px;">
                New Status: {{ ucfirst($ticket->status) }}
            </span>
        </div>

        <p>Subject: {{ $ticket->subject }}</p>

        <br>
        {{-- <a href="{{ route('store.support.show', $ticket->id) }}" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Ticket</a> --}}
        
        <p style="margin-top: 20px; color: #777; font-size: 12px;">Warehouse POS System Automation</p>
    </div>
</body>
</html>