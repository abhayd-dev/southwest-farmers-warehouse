<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Approval Required</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1a73e8, #0d47a1); color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 22px; margin-bottom: 6px; }
        .header p { font-size: 14px; opacity: 0.85; }
        .body { padding: 30px; }
        .po-box { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .po-box table { width: 100%; border-collapse: collapse; }
        .po-box td { padding: 8px 0; font-size: 14px; }
        .po-box td:first-child { color: #666; width: 45%; }
        .po-box td:last-child { font-weight: bold; }
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
        .items-table th { background: #1a73e8; color: #fff; padding: 8px 12px; text-align: left; }
        .items-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        .items-table tr:nth-child(even) td { background: #f9f9f9; }
        .btn-row { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 14px 36px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; margin: 0 10px; }
        .btn-approve { background: #28a745; color: #fff; }
        .btn-reject { background: #dc3545; color: #fff; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px; margin: 15px 0; font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📋 Purchase Order Approval Required</h1>
            <p>Your review is needed for PO #{{ $po->po_number }}</p>
        </div>

        <div class="body">
            <p style="margin-bottom: 15px;">You have been requested to review and approve the following Purchase Order:</p>

            <div class="po-box">
                <table>
                    <tr>
                        <td>PO Number:</td>
                        <td>#{{ $po->po_number }}</td>
                    </tr>
                    <tr>
                        <td>Vendor:</td>
                        <td>{{ $po->vendor->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Order Date:</td>
                        <td>{{ $po->order_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td style="color: #1a73e8; font-size: 18px;">${{ number_format($po->total_amount, 2) }}</td>
                    </tr>
                    @if($po->expected_delivery_date)
                    <tr>
                        <td>Expected Delivery:</td>
                        <td>{{ $po->expected_delivery_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($po->notes)
                    <tr>
                        <td>Notes:</td>
                        <td>{{ $po->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <h3 style="font-size: 15px; margin-bottom: 10px;">Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                        <td>{{ $item->requested_quantity }}</td>
                        <td>${{ number_format($item->unit_cost, 2) }}</td>
                        <td>${{ number_format($item->total_cost, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="btn-row">
                <a href="{{ $approveUrl }}" class="btn btn-approve">✓ Approve PO</a>
                <a href="{{ $rejectUrl }}" class="btn btn-reject">✗ Reject PO</a>
            </div>

            <div class="warning">
                ⚠️ These approval links are valid for <strong>7 days</strong>. After that, they will expire and a new approval request will need to be sent.
            </div>
        </div>

        <div class="footer">
            This email was sent by the Warehouse Management System.<br>
            If you did not expect this email, please contact your warehouse administrator.
        </div>
    </div>
</body>
</html>
