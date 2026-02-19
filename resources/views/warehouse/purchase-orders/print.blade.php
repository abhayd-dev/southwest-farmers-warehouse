<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            border-bottom: 3px solid #206bc4;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #206bc4;
            margin: 0;
        }
        .po-title {
            font-size: 18pt;
            font-weight: bold;
            text-align: right;
            margin: 0;
        }
        .po-number {
            font-size: 14pt;
            text-align: right;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #206bc4;
            border-bottom: 2px solid #206bc4;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 30%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #206bc4;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:last-child td {
            border-bottom: 2px solid #206bc4;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 20px;
            float: right;
            width: 40%;
        }
        .totals-table {
            width: 100%;
        }
        .totals-table td {
            padding: 5px;
        }
        .grand-total {
            font-size: 14pt;
            font-weight: bold;
            border-top: 2px solid #206bc4;
            padding-top: 8px !important;
        }
        .notes-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-top: 20px;
            clear: both;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .signature-section {
            margin-top: 40px;
            clear: both;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            margin-top: 50px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <h1 class="company-name">{{ $warehouse->name ?? 'Southwest Farmers Warehouse' }}</h1>
                    <div style="margin-top: 5px;">
                        {{ $warehouse->address ?? config('app.warehouse_address', 'Warehouse Address') }}<br>
                        Phone: {{ $warehouse->phone ?? config('app.warehouse_phone', 'Phone Number') }}<br>
                        Email: {{ $warehouse->email ?? config('app.warehouse_email', 'email@warehouse.com') }}
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top;">
                    <h2 class="po-title">PURCHASE ORDER</h2>
                    <div class="po-number">#{{ $po->po_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <div class="section-title">VENDOR</div>
                    <strong style="font-size: 13pt;">{{ $po->vendor->name }}</strong><br>
                    @if($po->vendor->address)
                        {{ $po->vendor->address }}<br>
                    @endif
                    @if($po->vendor->phone)
                        Phone: {{ $po->vendor->phone }}<br>
                    @endif
                    @if($po->vendor->email)
                        Email: {{ $po->vendor->email }}
                    @endif
                </td>
                <td style="width: 50%;">
                    <div class="section-title">ORDER DETAILS</div>
                    <table style="width: 100%;">
                        <tr>
                            <td class="info-label">PO Number:</td>
                            <td>{{ $po->po_number }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Order Date:</td>
                            <td>{{ $po->order_date->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Expected Delivery:</td>
                            <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('F d, Y') : 'TBD' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Payment Terms:</td>
                            <td>{{ $po->vendor->payment_terms ?? 'Net 30' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">ORDER ITEMS</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">UPC</th>
                    <th style="width: 40%;">Product Description</th>
                    <th style="width: 10%;" class="text-center">Quantity</th>
                    <th style="width: 15%;" class="text-right">Unit Price</th>
                    <th style="width: 15%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($po->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->barcode ?? 'N/A' }}</td>
                    <td><strong>{{ $item->product->product_name }}</strong></td>
                    <td class="text-center">{{ $item->requested_quantity }}</td>
                    <td class="text-right">${{ number_format($item->cost_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->requested_quantity * $item->cost_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">${{ number_format($po->total_amount - $po->tax_amount - $po->other_costs, 2) }}</td>
            </tr>
            @if($po->tax_amount > 0)
            <tr>
                <td>Tax:</td>
                <td class="text-right">${{ number_format($po->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($po->other_costs > 0)
            <tr>
                <td>Other Costs:</td>
                <td class="text-right">${{ number_format($po->other_costs, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td><strong>GRAND TOTAL:</strong></td>
                <td class="text-right"><strong>${{ number_format($po->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    @if($po->notes)
    <div class="notes-box">
        <strong>Special Instructions / Notes:</strong><br>
        {{ $po->notes }}
    </div>
    @endif

    <div class="signature-section">
        <table style="width: 100%;">
            <tr>
                <td class="signature-box">
                    <div class="signature-line">
                        <strong>Authorized By</strong><br>
                        {{ $warehouse->name ?? 'Southwest Farmers Warehouse' }}
                    </div>
                </td>
                <td style="width: 10%;"></td>
                <td class="signature-box">
                    <div class="signature-line">
                        <strong>Vendor Acknowledgment</strong><br>
                        {{ $po->vendor->name }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>Please confirm receipt of this purchase order and provide delivery confirmation.</p>
        <p style="margin-top: 10px; font-size: 8pt;">
            This is a computer-generated document. For questions, contact {{ $warehouse->phone ?? config('app.warehouse_phone') }} or {{ $warehouse->email ?? config('app.warehouse_email') }}
        </p>
    </div>
</body>
</html>
