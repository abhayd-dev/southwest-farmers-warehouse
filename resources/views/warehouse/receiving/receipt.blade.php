<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receiving Receipt - {{ $po->po_number }}</title>
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
                    <h2 class="po-title">RECEIVED ORDERS</h2>
                    <div class="po-number">#PO-{{ explode('-', $po->po_number)[1] ?? $po->po_number }}</div>
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
                    @if ($po->vendor->address)
                        {{ $po->vendor->address }}<br>
                    @endif
                    @if ($po->vendor->phone)
                        Phone: {{ $po->vendor->phone }}<br>
                    @endif
                    @if ($po->vendor->email)
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
                            <td class="info-label">RECEIVED DELIVERY:</td>
                            <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('F d, Y') : ($po->updated_at ? $po->updated_at->format('F d, Y') : 'TBD') }}
                            </td>
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
        <div class="section-title">RECEIVED ITEMS</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">UPC</th>
                    <th style="width: 40%;">Product Description</th>
                    <th style="width: 40%;" class="text-center">Quantity Ordered and Quantity Received</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->items as $index => $item)
                    @if ($item->received_quantity > 0)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->upc ?? 'N/A' }}</td>
                            <td><strong>{{ $item->product->product_name }}</strong></td>
                            <td class="text-center">{{ $item->requested_quantity }} Ordered /
                                {{ $item->received_quantity }} Received</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <table style="width: 100%;">
            <tr>
                <td class="signature-box">
                    <div class="signature-line">
                        <strong>Received By</strong><br>
                        {{ $warehouse->name ?? 'Central Warehouse' }}
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
</body>

</html>
