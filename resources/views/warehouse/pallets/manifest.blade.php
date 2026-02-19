<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pallet Manifest — {{ $pallet->pallet_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; font-weight: bold; }
        .header h2 { font-size: 16px; color: #555; margin-top: 4px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .meta-box { border: 1px solid #ddd; padding: 10px 15px; border-radius: 4px; }
        .meta-box label { font-size: 10px; text-transform: uppercase; color: #888; display: block; margin-bottom: 4px; }
        .meta-box strong { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #333; color: #fff; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .totals-row td { font-weight: bold; background: #f0f0f0; border-top: 2px solid #333; }
        .weight-bar-container { margin-bottom: 20px; }
        .weight-bar-bg { background: #eee; border-radius: 4px; height: 16px; }
        .weight-bar-fill { background: {{ ($pallet->total_weight / $pallet->max_weight) >= 0.9 ? '#dc3545' : (($pallet->total_weight / $pallet->max_weight) >= 0.7 ? '#ffc107' : '#28a745') }}; height: 16px; border-radius: 4px; width: {{ min(100, round(($pallet->total_weight / $pallet->max_weight) * 100)) }}%; }
        .footer { text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; margin-top: 20px; }
        .signature-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 40px; }
        .signature-box { border-top: 1px solid #333; padding-top: 8px; text-align: center; font-size: 10px; color: #666; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>

    <div class="header">
        <h1>📦 PALLET MANIFEST</h1>
        <h2>{{ $pallet->pallet_number }}</h2>
    </div>

    <div class="meta-grid">
        <div class="meta-box">
            <label>Pallet Number</label>
            <strong>{{ $pallet->pallet_number }}</strong>
        </div>
        <div class="meta-box">
            <label>Status</label>
            <strong>{{ strtoupper(str_replace('_', ' ', $pallet->status)) }}</strong>
        </div>
        <div class="meta-box">
            <label>Department</label>
            <strong>{{ $pallet->department->name ?? 'N/A' }}</strong>
        </div>
        <div class="meta-box">
            <label>Linked Store PO</label>
            <strong>{{ $pallet->storePO->po_number ?? 'Standalone' }}</strong>
        </div>
        <div class="meta-box">
            <label>Destination Store</label>
            <strong>{{ $pallet->storePO->store->store_name ?? 'N/A' }}</strong>
        </div>
        <div class="meta-box">
            <label>Date Created</label>
            <strong>{{ $pallet->created_at->format('d M Y') }}</strong>
        </div>
    </div>

    {{-- Weight Summary --}}
    <div class="weight-bar-container">
        <p style="margin-bottom: 6px; font-weight: bold;">
            Total Weight: {{ number_format($pallet->total_weight, 2) }} lbs / {{ number_format($pallet->max_weight, 0) }} lbs max
            ({{ min(100, round(($pallet->total_weight / $pallet->max_weight) * 100)) }}% full)
        </p>
        <div class="weight-bar-bg">
            <div class="weight-bar-fill"></div>
        </div>
    </div>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th style="text-align:center;">Quantity</th>
                <th style="text-align:right;">Wt/Unit (lbs)</th>
                <th style="text-align:right;">Total Weight (lbs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pallet->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                    <td>{{ $item->product->sku ?? '—' }}</td>
                    <td style="text-align:center;">{{ $item->quantity }}</td>
                    <td style="text-align:right;">{{ number_format($item->weight_per_unit, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($item->total_weight, 2) }}</td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="3">TOTAL</td>
                <td style="text-align:center;">{{ $pallet->items->sum('quantity') }}</td>
                <td></td>
                <td style="text-align:right;">{{ number_format($pallet->total_weight, 2) }} lbs</td>
            </tr>
        </tbody>
    </table>

    @if($pallet->notes)
        <p><strong>Notes:</strong> {{ $pallet->notes }}</p>
    @endif

    {{-- Signatures --}}
    <div class="signature-row">
        <div class="signature-box">Packed By</div>
        <div class="signature-box">Checked By</div>
        <div class="signature-box">Driver Signature</div>
    </div>

    <div class="footer">
        Printed on {{ now()->format('d M Y, H:i') }} &bull; Warehouse Management System
    </div>

    <script>window.onload = () => window.print();</script>
</body>
</html>
