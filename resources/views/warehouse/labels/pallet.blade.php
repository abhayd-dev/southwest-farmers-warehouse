<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pallet Label - {{ $data['pallet_id'] }}</title>
    {{-- JsBarcode Library --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @media print {
            @page {
                size: 4in 6in; /* Standard Thermal Label Size */
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #eee;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .label-container {
            width: 3.8in; /* Slightly less than 4in for padding safely */
            height: 5.8in;
            background: white;
            padding: 0.1in;
            box-sizing: border-box;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden; /* Ensure nothing spills out anyway */
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title {
            font-size: 24px;
            font-weight: 900;
            margin: 5px 0;
        }

        .content {
            flex-grow: 1;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 4px;
        }

        .label {
            font-size: 12px;
            color: #555;
            font-weight: bold;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .big-value {
            font-size: 18px;
            font-weight: 900;
        }

        .footer {
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 10px;
            /* Ensure footer doesn't overflow horizontally */
            width: 100%;
            overflow: hidden;
        }

        .pallet-id {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 5px;
            white-space: nowrap; /* Prevent ID from wrapping awkwardly */
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- FIX: Barcode Constraint --- */
        #barcode {
            max-width: 100%; /* Force barcode to fit within container width */
            height: auto;    /* Maintain aspect ratio if width shrinks */
            display: inline-block; /* Ensures it respects text-align: center */
        }
        /* ------------------------------- */

        /* Print Button Style */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .print-btn:hover { background: #0056b3; }
    </style>
</head>
<body>

    <button class="print-btn no-print" onclick="window.print()">Print Label</button>

    <div class="label-container">
        {{-- Header --}}
        <div class="header">
            <div class="store-name">Warehouse</div>
            <div class="title">PALLET TAG</div>
            <div class="label">Date: {{ $data['date'] }}</div>
        </div>

        {{-- Main Content --}}
        <div class="content">
            <div class="row" style="display: block; border: none;">
                <div class="label">Product Name</div>
                <div class="value big-value" style="font-size: 20px; line-height: 1.2;">
                    {{ $data['product_name'] }}
                </div>
            </div>

            <div class="row">
                <div>
                    <div class="label">SKU</div>
                    <div class="value">{{ $data['sku'] }}</div>
                </div>
                <div style="text-align: right;">
                    <div class="label">Category</div>
                    <div class="value">{{ $data['category'] }}</div>
                </div>
            </div>

            <div class="row">
                <div>
                    <div class="label">Quantity</div>
                    <div class="value big-value">{{ $data['quantity'] }} {{ strtoupper($data['unit']) }}</div>
                </div>
                <div style="text-align: right;">
                    <div class="label">Total Weight</div>
                    <div class="value">{{ $data['weight'] }}</div>
                </div>
            </div>

            <div class="row">
                <div>
                    <div class="label">Batch / Lot</div>
                    <div class="value">{{ $data['batch_no'] }}</div>
                </div>
                <div style="text-align: right;">
                    <div class="label">Expiry Date</div>
                    <div class="value big-value">{{ $data['expiry'] }}</div>
                </div>
            </div>
        </div>

        {{-- Footer / Barcode --}}
        <div class="footer">
            {{-- Added title attribute so full ID is visible on hover if truncated --}}
            <div class="pallet-id" title="{{ $data['pallet_id'] }}">{{ $data['pallet_id'] }}</div>
            <svg id="barcode"></svg>
        </div>
    </div>

    <script>
        // Generate Barcode
        JsBarcode("#barcode", "{{ $data['pallet_id'] }}", {
            format: "CODE128",
            lineColor: "#000",
            width: 2, // If barcode is still too wide, try reducing this to 1.5 or 1
            height: 50,
            displayValue: false,
            margin: 0 // Ensure no extra margin around SVG itself
        });

        // Auto print logic (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>