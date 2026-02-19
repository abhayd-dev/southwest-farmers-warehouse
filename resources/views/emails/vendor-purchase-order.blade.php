<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #206bc4;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .company-info {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .po-details {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .po-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .po-details td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .po-details td:first-child {
            font-weight: bold;
            width: 30%;
            color: #206bc4;
        }
        .items-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background: white;
        }
        .items-table th {
            background: #206bc4;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table tr:hover {
            background: #f8f9fa;
        }
        .total-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 2px solid #206bc4;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 16px;
        }
        .total-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #206bc4;
            border-top: 2px solid #206bc4;
            padding-top: 12px;
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 12px;
        }
        .notes-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PURCHASE ORDER</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px;">{{ $po->po_number }}</p>
    </div>

    <div class="content">
        <div class="company-info">
            <h3 style="margin-top: 0; color: #206bc4;">From:</h3>
            <strong>Southwest Farmers Warehouse</strong><br>
            {{ config('app.warehouse_address', 'Warehouse Address') }}<br>
            Phone: {{ config('app.warehouse_phone', 'Phone Number') }}<br>
            Email: {{ config('app.warehouse_email', 'email@warehouse.com') }}
        </div>

        <div class="company-info">
            <h3 style="margin-top: 0; color: #206bc4;">To:</h3>
            <strong>{{ $po->vendor->name }}</strong><br>
            @if($po->vendor->address)
                {{ $po->vendor->address }}<br>
            @endif
            @if($po->vendor->phone)
                Phone: {{ $po->vendor->phone }}<br>
            @endif
            @if($po->vendor->email)
                Email: {{ $po->vendor->email }}
            @endif
        </div>

        <div class="po-details">
            <h3 style="margin-top: 0; color: #206bc4;">Order Details</h3>
            <table>
                <tr>
                    <td>PO Number:</td>
                    <td><strong>{{ $po->po_number }}</strong></td>
                </tr>
                <tr>
                    <td>Order Date:</td>
                    <td>{{ $po->order_date->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Expected Delivery:</td>
                    <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('F d, Y') : 'To be confirmed' }}</td>
                </tr>
                <tr>
                    <td>Payment Terms:</td>
                    <td>{{ $po->vendor->payment_terms ?? 'Net 30' }}</td>
                </tr>
            </table>
        </div>

        <h3 style="color: #206bc4;">Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>UPC</th>
                    <th>Product</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($po->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->barcode ?? 'N/A' }}</td>
                    <td><strong>{{ $item->product->product_name }}</strong></td>
                    <td style="text-align: center;">{{ $item->requested_quantity }}</td>
                    <td style="text-align: right;">${{ number_format($item->cost_price, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->requested_quantity * $item->cost_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($po->total_amount - $po->tax_amount - $po->other_costs, 2) }}</span>
            </div>
            @if($po->tax_amount > 0)
            <div class="total-row">
                <span>Tax:</span>
                <span>${{ number_format($po->tax_amount, 2) }}</span>
            </div>
            @endif
            @if($po->other_costs > 0)
            <div class="total-row">
                <span>Other Costs:</span>
                <span>${{ number_format($po->other_costs, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>GRAND TOTAL:</span>
                <span>${{ number_format($po->total_amount, 2) }}</span>
            </div>
        </div>

        @if($po->notes)
        <div class="notes-section">
            <strong>Special Instructions / Notes:</strong><br>
            {{ $po->notes }}
        </div>
        @endif

        <div style="background: white; padding: 20px; border-radius: 5px; margin-top: 20px;">
            <h4 style="color: #206bc4; margin-top: 0;">Delivery Instructions:</h4>
            <p>Please confirm receipt of this purchase order and provide an estimated delivery date.</p>
            <p>For any questions or concerns, please contact us at {{ config('app.warehouse_phone') }} or {{ config('app.warehouse_email') }}.</p>
        </div>
    </div>

    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>This is an automated email from Southwest Farmers Warehouse Management System.</p>
    </div>
</body>
</html>
