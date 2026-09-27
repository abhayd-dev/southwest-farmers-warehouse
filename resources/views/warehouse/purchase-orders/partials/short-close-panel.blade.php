{{-- Client 9/27: order completed short; the invoice covers only what was received. --}}
@if ($purchaseOrder->short_close_lines)
    @php
        $shortLines = collect($purchaseOrder->short_close_lines);
        $reduction = $shortLines->sum(fn ($l) => ($l['ordered'] - $l['received']) * $l['unit_cost']);
        $fmtQty = fn ($q) => rtrim(rtrim(number_format((float) $q, 2, '.', ''), '0'), '.');
    @endphp
    <div class="alert alert-secondary border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="mdi mdi-package-variant-minus fs-4"></i>
            <div class="flex-grow-1">
                <strong>Completed short</strong> &mdash; the rest is not coming, so the invoice covers only what was received.
                <small class="d-block text-muted">
                    By {{ $purchaseOrder->short_closed_by }} on {{ $purchaseOrder->short_closed_at?->displayTime()->format('M d, Y h:i A') }}.
                    Invoice reduced by ${{ number_format($reduction, 2) }} to ${{ number_format($purchaseOrder->total_amount, 2) }}.
                </small>
            </div>
        </div>
        <table class="table table-sm mb-0 mt-2 bg-white rounded">
            <thead><tr><th>Product</th><th class="text-center">Ordered</th><th class="text-center">Received (invoiced)</th><th class="text-center">Not delivered</th><th class="text-end">Taken off invoice</th></tr></thead>
            <tbody>
                @foreach ($shortLines as $line)
                    <tr>
                        <td>{{ $line['product'] }}</td>
                        <td class="text-center">{{ $fmtQty($line['ordered']) }}</td>
                        <td class="text-center fw-bold">{{ $fmtQty($line['received']) }}</td>
                        <td class="text-center text-danger">&minus;{{ $fmtQty($line['ordered'] - $line['received']) }}</td>
                        <td class="text-end">${{ number_format(($line['ordered'] - $line['received']) * $line['unit_cost'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
