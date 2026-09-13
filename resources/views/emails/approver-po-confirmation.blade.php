<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO Approved</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background-color: #28a745; color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 20px; margin-bottom: 6px; }
        .body { padding: 30px; font-size: 14px; line-height: 1.6; }
        .po-box { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .po-box table { width: 100%; border-collapse: collapse; }
        .po-box td { padding: 6px 0; font-size: 14px; }
        .po-box td:first-child { color: #666; width: 45%; }
        .po-box td:last-child { font-weight: bold; }
        .btn-row { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; background-color: #dc3545; color: #fff; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>You Approved This Purchase Order</h1>
            <p>#{{ $po->po_number }}</p>
        </div>

        <div class="body">
            <p>This confirms your approval, and the order has been sent to <strong>{{ $po->vendor->name ?? 'the vendor' }}</strong>.</p>

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
                </table>
            </div>

            <p>If something changed and this order needs to be stopped, you can still cancel it below — this works even after the vendor has received their copy, as long as receiving hasn't started yet.</p>

            <div class="btn-row">
                <a href="{{ $cancelUrl }}" class="btn">Cancel This Order</a>
            </div>

            <p style="font-size: 12px; color: #888;">This cancel link is valid for 14 days.</p>
        </div>

        <div class="footer">
            This email was sent by the Warehouse Management System.
        </div>
    </div>
</body>
</html>
