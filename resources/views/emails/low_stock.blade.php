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
    </style>
</head>
<body>
    <div class="box">
        <h3>⚠️ Low Stock Notification</h3>
        <p>The following items have fallen below the threshold level.</p>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Total Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr>
                    <td>{{ $p->product_name }}</td>
                    <td>{{ $p->sku }}</td>
                    <td class="alert">{{ $p->total_stock }} {{ $p->unit }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 20px;">
            <a href="{{ route('warehouse.products.index') }}">Login to Warehouse</a>
        </p>
    </div>
</body>
</html>