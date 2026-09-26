<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Over-receipt approval</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 640px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="background: #f59f00; color: #fff; padding: 24px 30px;">
            <h1 style="font-size: 20px; margin: 0 0 4px;">More received than ordered</h1>
            <p style="margin: 0;">Purchase Order #{{ $po->po_number }} &middot; {{ $po->vendor->name ?? '' }}</p>
        </div>
        <div style="padding: 24px 30px; font-size: 14px; line-height: 1.6;">
            <p>The warehouse received more than was ordered on the lines below. The stock has been received.
               Please approve paying for the extra quantity, or reject to keep the invoice at the ordered quantity.</p>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin: 16px 0;">
                <thead>
                    <tr style="background: #f1f3f5;">
                        <th style="text-align: left; padding: 8px;">Product</th>
                        <th style="text-align: center; padding: 8px;">Ordered</th>
                        <th style="text-align: center; padding: 8px;">Received</th>
                        <th style="text-align: center; padding: 8px;">Extra</th>
                        <th style="text-align: right; padding: 8px;">Extra cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($po->over_receipt_lines ?? [] as $line)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $line['product'] }}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">{{ $line['ordered'] }}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">{{ $line['received'] }}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center; font-weight: bold;">+{{ $line['received'] - $line['ordered'] }}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">${{ number_format(($line['received'] - $line['ordered']) * $line['unit_cost'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($po->invoice_document)
                <p><a href="{{ $po->invoice_document_url }}">View the attached vendor invoice</a></p>
            @endif

            <p style="text-align: center; margin: 28px 0 10px;">
                <a href="{{ $approveUrl }}" style="background: #2fb344; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-right: 10px;">Approve</a>
                <a href="{{ $rejectUrl }}" style="background: #d63939; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold;">Reject</a>
            </p>
            <p style="color: #888; font-size: 12px; text-align: center;">These links expire in 14 days.</p>
        </div>
    </div>
</body>
</html>
