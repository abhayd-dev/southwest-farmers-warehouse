<x-app-layout title="PO #{{ $purchaseOrder->po_number }}">
    <div class="container-fluid">
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">PO: {{ $purchaseOrder->po_number }}</h4>
                <span class="badge bg-{{ $purchaseOrder->status == 'completed' ? 'success' : 'warning' }} fs-6">
                    {{ strtoupper($purchaseOrder->status) }}
                </span>
            </div>
            <div class="d-flex gap-2">

                {{-- NEW: Print Labels Button (Visible if items received) --}}
                @if ($purchaseOrder->items->sum('received_quantity') > 0)
                    <a href="{{ route('warehouse.purchase-orders.labels', $purchaseOrder->id) }}" class="btn btn-dark"
                        target="_blank">
                        <i class="mdi mdi-barcode-scan me-1"></i> Print Labels
                    </a>
                @endif

                @if ($purchaseOrder->status === 'draft')
                    <form action="{{ route('warehouse.purchase-orders.mark-ordered', $purchaseOrder->id) }}"
                        method="POST">
                        @csrf
                        <button class="btn btn-info text-white"><i class="mdi mdi-send"></i> Mark as Ordered</button>
                    </form>
                @endif
                <a href="{{ route('warehouse.purchase-orders.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        {{-- INFO CARDS --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Vendor</h6>
                        <h5 class="fw-bold">{{ $purchaseOrder->vendor->name }}</h5>
                        <div><i class="mdi mdi-email me-1"></i> {{ $purchaseOrder->vendor->email ?? '-' }}</div>
                        <div><i class="mdi mdi-phone me-1"></i> {{ $purchaseOrder->vendor->phone ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Order Summary</h6>
                        <div class="d-flex justify-content-between">
                            <span>Order Date:</span>
                            <span class="fw-semibold">{{ $purchaseOrder->order_date->format('d M, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Total Amount:</span>
                            <span class="fw-bold text-success fs-5">₹
                                {{ number_format($purchaseOrder->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECEIVE SECTION --}}
        @if (
            $purchaseOrder->status !== 'draft' &&
                $purchaseOrder->status !== 'completed' &&
                $purchaseOrder->status !== 'cancelled')
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3"><i class="mdi mdi-truck-delivery"></i> Receive Items</h5>

                    <form action="{{ route('warehouse.purchase-orders.receive', $purchaseOrder->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Vendor Invoice Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="invoice_number" class="form-control" required
                                    placeholder="e.g. INV-9988">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Ordered</th>
                                        <th>Pending</th>
                                        <th style="width: 150px;">Receive Now</th>
                                        <th>Batch No.</th>
                                        <th>Expiry Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseOrder->items as $item)
                                        @if ($item->pending_quantity > 0)
                                            <tr>
                                                <td>{{ $item->product->product_name }} <br> <small
                                                        class="text-muted">{{ $item->product->sku }}</small></td>
                                                <td>{{ $item->requested_quantity }}</td>
                                                <td class="text-danger fw-bold">{{ $item->pending_quantity }}</td>
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $item->id }}][receive_qty]"
                                                        class="form-control" max="{{ $item->pending_quantity }}"
                                                        min="0" value="0">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        name="items[{{ $item->id }}][batch_number]"
                                                        class="form-control" placeholder="Auto if empty">
                                                </td>
                                                <td>
                                                    <input type="date"
                                                        name="items[{{ $item->id }}][expiry_date]"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-check-all"></i> Process
                                Receive</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ITEMS LIST (READ ONLY) --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light fw-bold">Order Items Details</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">Product</th>
                            <th>Unit Cost</th>
                            <th>Qty Ordered</th>
                            <th>Qty Received</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-4">{{ $item->product->product_name }}</td>
                                <td>₹ {{ number_format($item->unit_cost, 2) }}</td>
                                <td>{{ $item->requested_quantity }}</td>
                                <td
                                    class="fw-bold {{ $item->received_quantity == $item->requested_quantity ? 'text-success' : 'text-warning' }}">
                                    {{ $item->received_quantity }}
                                </td>
                                <td>₹ {{ number_format($item->total_cost, 2) }}</td>
                                <td>
                                    @if ($item->received_quantity >= $item->requested_quantity)
                                        <span class="badge bg-success">Full</span>
                                    @elseif($item->received_quantity > 0)
                                        <span class="badge bg-warning">Partial</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
