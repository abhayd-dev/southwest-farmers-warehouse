<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Received Order - {{ $po->po_number }}</title>
    <style>
        @page {
            margin: 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #206bc4;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 22pt;
            font-weight: bold;
            color: #206bc4;
            margin: 0;
        }

        .right-header {
            text-align: right;
            vertical-align: top;
        }

        .received-order-text {
            font-size: 10pt;
            color: #666;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .po-title {
            font-size: 18pt;
            font-weight: bold;
            color: #206bc4;
            margin: 0;
            text-transform: uppercase;
        }

        .po-number-small {
            font-size: 11pt;
            color: #444;
            margin-top: 2px;
        }

        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .details-table td {
            width: 50%;
            vertical-align: top;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #206bc4;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info-row {
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            width: 130px;
            display: inline-block;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #206bc4;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            width: 100%;
            margin-top: 10px;
        }

        .totals-table {
            float: right;
            width: 250px;
        }

        .totals-table td {
            padding: 4px 10px;
        }

        .grand-total {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            border-top: 1px solid #333;
            margin-top: 5px;
            padding-top: 5px;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
            clear: both;
        }

        .signature-box {
            text-align: left;
            padding-top: 40px;
            border-top: 1px solid #333;
            width: 200px;
        }

        .footer-note {
            margin-top: 60px;
            font-weight: bold;
            font-size: 11pt;
            text-align: left;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td>
                <h1 class="company-name">{{ $warehouse->name ?? 'Central Warehouse' }}</h1>
                <div style="margin-top: 8px; color: #555;">
                    {{ $warehouse->address ?? 'Main Industrial Area' }}<br>
                    Phone: {{ $warehouse->phone ?? '9999999999' }}<br>
                    Email: {{ $warehouse->email ?? 'warehouse@company.com' }}
                </div>
            </td>
            <td class="right-header">
                {{-- <div class="received-order-text"></div> --}}
                <div class="po-title">Received Orders</div>
                <div class="po-number-small">#{{ $po->po_number }}</div>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td>
                <div class="section-title">VENDOR</div>
                <div class="info-row"><strong>{{ $po->vendor->name }}</strong></div>
                @if ($po->vendor->address)
                    <div class="info-row">{{ $po->vendor->address }}</div>
                @endif
                @if ($po->vendor->phone)
                    <div class="info-row">Phone: {{ $po->vendor->phone }}</div>
                @endif
            </td>
            <td>
                <div class="section-title">ORDER DETAILS</div>
                <div class="info-row"><span class="info-label">PO Number:</span> {{ $po->po_number }}</div>
                <div class="info-row"><span class="info-label">Order Date:</span>
                    {{ $po->order_date->format('F d, Y') }}</div>
                <div class="info-row"><span class="info-label">RECEIVED DELIVERY:</span>
                    {{ $po->updated_at->format('F d, Y') }}</div>
                <div class="info-row"><span class="info-label">Payment Terms:</span>
                    {{ $po->vendor->payment_terms ?? 'Net 30' }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">UPC</th>
                <th style="width: 45%;">Product Description</th>
                {{-- <th style="width: 10%;" class="text-center">Quantity</th> --}}
                <th style="width: 12%;" class="text-right">Unit Price</th>
                <th style="width: 13%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal = 0;
                $count = 1;
            @endphp
            @foreach ($po->items as $item)
                @if ($item->received_quantity > 0)
                    @php
                        $price = $item->receiving_unit_cost ?? $item->unit_cost;
                        $lineTotal = $item->received_quantity * $price;
                        $subtotal += $lineTotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $count++ }}</td>
                        <td>{{ $item->product->upc ?? 'N/A' }}</td>
                        <td><strong>{{ $item->product->product_name }}</strong></td>
                        {{-- <td class="text-center">{{ $item->received_quantity }}</td> --}}
                        <td class="text-right">${{ number_format($price, 2) }}</td>
                        <td class="text-right">${{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right">${{ number_format($subtotal, 2) }}</td>
            </tr>
            @if ($po->taxes > 0)
                <tr>
                    <td class="text-right"><strong>Taxes:</strong></td>
                    <td class="text-right">${{ number_format($po->taxes, 2) }}</td>
                </tr>
            @endif
            @if ($po->duties > 0)
                <tr>
                    <td class="text-right"><strong>Duties:</strong></td>
                    <td class="text-right">${{ number_format($po->duties, 2) }}</td>
                </tr>
            @endif
            @if ($po->shipping_cost > 0)
                <tr>
                    <td class="text-right"><strong>Shipping Cost:</strong></td>
                    <td class="text-right">${{ number_format($po->shipping_cost, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-right grand-total">GRAND TOTAL:</td>
                <td class="text-right grand-total">
                    ${{ number_format($subtotal + $po->taxes + $po->duties + $po->shipping_cost, 2) }}</td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-top: 100px;">
        <tr>
            <td style="width: 10%;"></td>
            <td>
                <div class="signature-box">
                    <strong>Received By</strong><br>
                    <small>{{ $warehouse->name ?? 'Central Warehouse' }}</small>
                </div>
            </td>
            <td style="width: 20%;"></td>
            <td>
                <div class="signature-box">
                    <strong>Vendor Acknowledgment</strong><br>
                    <small>{{ $po->vendor->name }}</small>
                </div>
            </td>
            <td style="width: 10%;"></td>
        </tr>
    </table>

</body>

</html>
