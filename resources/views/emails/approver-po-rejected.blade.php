<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO Rejected</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background-color: #dc3545; color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 20px; margin-bottom: 6px; }
        .body { padding: 30px; font-size: 14px; line-height: 1.6; }
        .po-box { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .po-box table { width: 100%; border-collapse: collapse; }
        .po-box td { padding: 6px 0; font-size: 14px; }
        .po-box td:first-child { color: #666; width: 45%; }
        .po-box td:last-child { font-weight: bold; }
        .reason-box { background: #fff5f5; border-left: 4px solid #dc3545; padding: 12px 16px; margin: 15px 0; border-radius: 0 4px 4px 0; }
        .reason-box .label { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #dc3545; margin-bottom: 4px; }
        .reason-box .text { color: #555; font-style: italic; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>You Rejected This Purchase Order</h1>
            <p>#{{ $po->po_number }}</p>
        </div>

        <div class="body">
            <p>This email confirms that you rejected Purchase Order #<strong>{{ $po->po_number }}</strong>.</p>

            <div class="po-box">
                <table>
                    <tr>
                        <td>PO Number:</td>
                        <td>{{ $po->po_number }}</td>
                    </tr>
                    <tr>
                        <td>Vendor:</td>
                        <td>{{ $po->vendor->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td>${{ number_format($po->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Date Rejected:</td>
                        <td>{{ $po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>

            <div class="reason-box">
                <div class="label">Rejection Reason Provided:</div>
                <div class="text">{{ $po->approval_reason ?? 'No reason provided' }}</div>
            </div>

            <p style="margin-top: 20px; color: #666;">The warehouse administrators have been notified of your decision and the order has been updated accordingly.</p>
        </div>

        <div class="footer">
            This email was sent by the Warehouse Management System.
        </div>
    </div>
</body>
</html>
