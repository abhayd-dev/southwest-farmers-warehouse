<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PO-{{ $purchaseOrder->po_number }} Labels</title>
    <style>
        @page {
            margin: 15px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
        }
        
        /* Main Layout Grid */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .layout-table td {
            vertical-align: top;
            padding: 0 10px 15px 0; /* Gap between labels */
            width: 50%;
        }

        /* The Card Box */
        .label-card {
            width: 100%;
            height: 200px; /* Fixed Height */
            border: 2px solid #000;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
        }

        /* Inner Structure Table */
        .card-structure {
            width: 100%;
            border-collapse: collapse;
        }

        /* 1. Black Header */
        .header-row td {
            background-color: #000;
            color: #fff;
            padding: 6px 10px;
            border-bottom: 1px solid #000;
        }
        .header-title {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-date {
            font-size: 9px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        /* 2. Product Details */
        .product-row td {
            padding: 10px 12px 5px 12px;
            vertical-align: top;
        }
        .product-name {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.2;
            height: 32px; /* Fixed height for 2 lines */
            overflow: hidden;
            margin-bottom: 8px;
            color: #222;
            display: block;
        }
        .sku-badge {
            background-color: #000;
            color: #fff;
            padding: 3px 6px;
            font-size: 12px;
            font-weight: 900;
            border-radius: 4px;
            display: inline-block;
            font-family: 'Courier New', Courier, monospace;
        }

        /* 3. Vendor Meta Info */
        .meta-row td {
            padding: 5px 12px;
            border-top: 1px solid #ddd;
        }
        .meta-table { width: 100%; }
        .meta-label {
            font-size: 7px;
            text-transform: uppercase;
            color: #777;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .meta-value {
            font-size: 10px;
            font-weight: 700;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 4. Barcode Section */
        .barcode-row td {
            padding-top: 5px;
            padding-bottom: 8px;
            text-align: center;
            vertical-align: bottom;
            height: 50px;
        }
        .barcode-img {
            height: 35px;
            max-width: 90%;
        }
        .barcode-num {
            font-size: 9px;
            letter-spacing: 3px;
            margin-top: 1px;
            font-family: 'Courier New', Courier, monospace;
        }

        /* Utility */
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <table class="layout-table">
        @foreach($items->chunk(2) as $row)
            <tr>
                @foreach($row as $item)
                    <td>
                        <div class="label-card">
                            <table class="card-structure">
                                
                                <tr class="header-row">
                                    <td colspan="2">
                                        <table style="width: 100%">
                                            <tr>
                                                <td class="header-title" style="border:none; padding:0; background:transparent;">Warehouse Receipt</td>
                                                <td class="header-date" style="border:none; padding:0; background:transparent;">REC: {{ date('d-M-y') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr class="product-row">
                                    <td colspan="2">
                                        <div class="product-name">
                                            {{ Str::limit($item->product->product_name, 45) }}
                                        </div>
                                        <span class="sku-badge">{{ $item->product->sku }}</span>
                                    </td>
                                </tr>

                                <tr class="meta-row">
                                    <td colspan="2">
                                        <table class="meta-table">
                                            <tr>
                                                <td style="width: 55%; padding:0; border:none;">
                                                    <div class="meta-label">Vendor</div>
                                                    <div class="meta-value">{{ Str::limit($purchaseOrder->vendor->name, 18) }}</div>
                                                </td>
                                                <td style="width: 45%; padding:0; border:none;" class="text-right">
                                                    <div class="meta-label">PO Ref</div>
                                                    <div class="meta-value">{{ $purchaseOrder->po_number }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr class="barcode-row">
                                    <td colspan="2">
                                        @php
                                            $code = $item->product->sku;
                                            $barcodeData = $generator->getBarcode($code, $generator::TYPE_CODE_128);
                                            $base64 = base64_encode($barcodeData);
                                        @endphp
                                        <img src="data:image/png;base64,{{ $base64 }}" class="barcode-img">
                                        <div class="barcode-num">{{ $code }}</div>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </td>
                @endforeach

                {{-- Empty cell to maintain grid if odd number of items --}}
                @if($row->count() == 1) 
                    <td></td> 
                @endif
            </tr>
        @endforeach
    </table>

</body>
</html>