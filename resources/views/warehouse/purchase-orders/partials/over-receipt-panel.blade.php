{{-- Over-receipt flag and approval (client PDF 9/24, Warehouse item 1). --}}
@if ($purchaseOrder->over_receipt_status)
    @php
        $pending = $purchaseOrder->over_receipt_status === \App\Models\PurchaseOrder::OVER_RECEIPT_PENDING;
        $approved = $purchaseOrder->over_receipt_status === \App\Models\PurchaseOrder::OVER_RECEIPT_APPROVED;
    @endphp
    <div class="alert {{ $pending ? 'alert-warning' : ($approved ? 'alert-success' : 'alert-secondary') }} border-0 shadow-sm mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h6 class="fw-bold mb-1">
                    <i class="mdi mdi-alert-decagram me-1"></i>
                    @if ($pending)
                        Over-receipt: awaiting approval
                    @elseif ($approved)
                        Over-receipt approved
                    @else
                        Over-receipt rejected
                    @endif
                </h6>
                <small>
                    @if ($pending)
                        More was received than ordered. The stock is in; the approver{{ $purchaseOrder->approval_email ? ' (' . $purchaseOrder->approval_email . ')' : '' }} decides whether the invoice covers the extra.
                    @else
                        By {{ $purchaseOrder->over_receipt_decided_by }} on {{ $purchaseOrder->over_receipt_decided_at?->displayTime()->format('M d, Y h:i A') }}.
                        {{ $approved ? 'Invoice reflects the quantity received.' : 'Invoice kept at the quantity ordered.' }}
                    @endif
                </small>
            </div>
            @if ($pending && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('approve_po')))
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('warehouse.purchase-orders.over-receipt', $purchaseOrder) }}">
                        @csrf
                        <input type="hidden" name="decision" value="approve">
                        <button class="btn btn-sm btn-success"><i class="mdi mdi-check"></i> Approve</button>
                    </form>
                    <form method="POST" action="{{ route('warehouse.purchase-orders.over-receipt', $purchaseOrder) }}"
                          onsubmit="return confirm('Reject? The invoice will stay at the quantity originally ordered.')">
                        @csrf
                        <input type="hidden" name="decision" value="reject">
                        <button class="btn btn-sm btn-outline-danger"><i class="mdi mdi-close"></i> Reject</button>
                    </form>
                </div>
            @endif
        </div>
        <table class="table table-sm mb-0 mt-2 bg-white rounded">
            <thead><tr><th>Product</th><th class="text-center">Ordered</th><th class="text-center">Received</th><th class="text-center">Extra</th><th class="text-end">Extra cost</th></tr></thead>
            <tbody>
                @foreach ($purchaseOrder->over_receipt_lines ?? [] as $line)
                    <tr>
                        <td>{{ $line['product'] }}</td>
                        <td class="text-center">{{ $line['ordered'] }}</td>
                        <td class="text-center">{{ $line['received'] }}</td>
                        <td class="text-center fw-bold">+{{ $line['received'] - $line['ordered'] }}</td>
                        <td class="text-end">${{ number_format(($line['received'] - $line['ordered']) * $line['unit_cost'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
