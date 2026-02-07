<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
        .box { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .alert { color: #d9534f; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #eee; }
        .badge { background: #ffc107; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="box">
        <h3>🚨 Late Delivery Notification</h3>
        <p>The following Purchase Orders have passed their expected delivery date and are still pending.</p>

        <table>
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>Expected Date</th>
                    <th>Days Overdue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders as $po)
                <tr>
                    <td><strong>{{ $po->po_number }}</strong></td>
                    <td>{{ $po->vendor->name ?? 'Unknown' }}</td>
                    <td>{{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y') }}</td>
                    <td class="alert">
                        {{ \Carbon\Carbon::parse($po->expected_delivery_date)->diffInDays(now()) }} Days
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 20px;">
            <a href="{{ route('warehouse.purchase-orders.index') }}">View Purchase Orders</a>
        </p>
    </div>
</body>
</html>