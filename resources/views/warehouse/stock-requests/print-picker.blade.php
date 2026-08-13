<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Store PO Picker Sheet - PO #{{ $stockRequest->id }}</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 16pt; }
        .info-bar { display: flex; justify-content: space-between; margin-bottom: 15px; font-weight: bold; font-size: 11pt; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { border: 1.5px solid #000; padding: 8px; background: #e2e8f0; text-align: left; font-size: 10pt; text-transform: uppercase; }
        .table td { border: 1px solid #000; padding: 8px; font-size: 10.5pt; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; border-top: 1px dashed #000; padding-top: 10px; display: flex; justify-content: space-between; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>STORE PO PICKER MANIFEST</h2>
        <div style="margin-top: 4px; font-size: 10pt;">Southwest Farmers Market Warehouse</div>
    </div>

    <table style="width: 100%; margin-bottom: 15px; font-size: 11pt;">
        <tr>
            <td><strong>DATE:</strong> {{ optional($stockRequest->created_at)->format('m/d/Y') }}</td>
            <td style="text-align: right;"><strong>STORE NAME:</strong> {{ strtoupper($stockRequest->store->store_name ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td><strong>PO NUMBER:</strong> PO-{{ str_pad($stockRequest->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td style="text-align: right;"><strong>TOTAL ITEMS:</strong> {{ $stockRequest->items->count() ?: 1 }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">UPC / SKU</th>
                <th style="width: 50%;">PRODUCT DESCRIPTION</th>
                <th style="width: 20%;" class="text-center">QTY SENT</th>
            </tr>
        </thead>
        <tbody>
            @if($stockRequest->items && $stockRequest->items->count() > 0)
                @foreach($stockRequest->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->product->upc ?? $item->product->sku ?? 'NO CODE' }}</strong></td>
                        <td>{{ strtoupper($item->product->product_name ?? 'N/A') }}</td>
                        <td class="text-center font-weight-bold" style="font-size: 13pt;">{{ $item->quantity_requested }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td><strong>{{ $stockRequest->product->upc ?? $stockRequest->product->sku ?? 'NO CODE' }}</strong></td>
                    <td>{{ strtoupper($stockRequest->product->product_name ?? 'N/A') }}</td>
                    <td class="text-center font-weight-bold" style="font-size: 13pt;">{{ $stockRequest->quantity_requested }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <div>Picker Signature: _______________________</div>
        <div>Supervisor Verification: _______________________</div>
    </div>
</body>
</html>
