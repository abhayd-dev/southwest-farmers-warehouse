<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Store Order</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background-color: #1a73e8; color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 20px; margin-bottom: 6px; }
        .body { padding: 30px; font-size: 14px; line-height: 1.6; }
        .po-box { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .po-box table { width: 100%; border-collapse: collapse; }
        .po-box td { padding: 6px 0; font-size: 14px; }
        .po-box td:first-child { color: #666; width: 45%; }
        .po-box td:last-child { font-weight: bold; }
        .btn-row { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 14px 36px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; background-color: #1a73e8; color: #fff; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Incoming Store Order</h1>
            <p>#{{ $storeOrder->po_number }}</p>
        </div>

        <div class="body">
            <p>A new store order needs warehouse attention.</p>

            <div class="po-box">
                <table>
                    <tr>
                        <td>Store:</td>
                        <td>{{ $storeOrder->store->store_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Order #:</td>
                        <td>{{ $storeOrder->po_number }}</td>
                    </tr>
                    <tr>
                        <td>Items:</td>
                        <td>{{ $storeOrder->items->count() }}</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>{{ strtoupper($storeOrder->status) }}</td>
                    </tr>
                </table>
            </div>

            <p>Full order details are attached as a PDF.</p>

            <div class="btn-row">
                <a href="{{ route('warehouse.store-orders.show', $storeOrder->id) }}" class="btn">View Order</a>
            </div>
        </div>

        <div class="footer">
            This email was sent by the Warehouse Management System.
        </div>
    </div>
</body>
</html>
