<!DOCTYPE html>
<html>
<head>
    <title>New Reply on Ticket</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #fff; padding: 20px; border-radius: 5px; max-width: 600px; margin: auto;">
        <h2 style="color: #333;">New Reply on Ticket #{{ $ticket->ticket_number }}</h2>
        
        <p><strong>From:</strong> {{ $msg->sender->name ?? 'Support Team' }}</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;">
            <p style="margin: 0;">{{ $msg->message }}</p>
        </div>

        <p>Please login to your dashboard to view the full conversation and reply.</p>

        {{-- Logic to determine link based on who is receiving (simple generic link or specific) --}}
        <p><a href="{{ url('/login') }}" style="color: #007bff;">Click here to login</a></p>
        
        <p style="margin-top: 20px; color: #777; font-size: 12px;">Warehouse POS System Automation</p>
    </div>
</body>
</html>