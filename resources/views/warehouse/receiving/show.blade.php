@extends('layouts.app')

@section('title', 'Receive Purchase Order #' . $purchaseOrder->po_number)

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col-12 d-flex justify-content-between">
                <h4 class="mb-0 font-weight-bold text-dark">
                    <i class="mdi mdi-truck-check text-primary me-2"></i> Receive Order: {{ $purchaseOrder->po_number }}
                </h4>
                <div class="d-flex gap-2">
                    @if ($purchaseOrder->invoice_document)
                        <a href="{{ $purchaseOrder->invoice_document_url }}" target="_blank"
                            class="btn btn-outline-info shadow-sm">
                            <i class="mdi mdi-receipt me-1"></i> View Invoice Picture
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-info shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal">
                            <i class="mdi mdi-camera me-1"></i> Attach Invoice Picture
                        </button>
                    @endif
                    @if ($purchaseOrder->status == 'completed')
                        <a href="{{ route('warehouse.receiving.receipt', $purchaseOrder->id) }}" target="_blank"
                            class="btn btn-outline-primary shadow-sm">
                            <i class="mdi mdi-printer me-1"></i> Print Receiving Order
                        </a>
                    @endif
                    <a href="{{ route('warehouse.receiving.index') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Received Orders
                    </a>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-12">
                {{-- SUMMARY CARDS --}}
                <div class="row g-4 mb-4">
                    {{-- Vendor Info --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 position-relative rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.5px;">
                                            Vendor Details</h6>
                                        <h5 class="fw-bold text-dark mb-0">{{ $purchaseOrder->vendor->name }}</h5>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-domain fs-4"></i>
                                    </div>
                                </div>
                                <hr class="text-muted opacity-25 my-3">
                                <div class="d-flex flex-column gap-2 text-dark">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-email-outline text-muted me-2 fs-5"></i>
                                        <span>{{ $purchaseOrder->vendor->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-phone-outline text-muted me-2 fs-5"></i>
                                        <span>{{ $purchaseOrder->vendor->phone ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Info --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 position-relative rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.5px;">
                                            Order Summary</h6>
                                        <h5 class="fw-bold text-dark mb-0">Total:
                                            ${{ number_format($purchaseOrder->total_amount, 2) }}</h5>
                                    </div>
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-file-document-outline fs-4"></i>
                                    </div>
                                </div>
                                <hr class="text-muted opacity-25 my-3">
                                <div class="d-flex flex-column gap-2 text-dark">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="mdi mdi-calendar me-1"></i> Order Date:</span>
                                        <span
                                            class="fw-medium">{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d M Y') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="mdi mdi-account me-1"></i> Requested By:</span>
                                        <span class="fw-medium">{{ $purchaseOrder->creator->name ?? 'System' }}</span>
                                    </div>
                                    @if ($purchaseOrder->vendor_invoice_number)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted"><i class="mdi mdi-receipt me-1"></i> Vendor Invoice:</span>
                                            <span class="fw-semibold">{{ $purchaseOrder->vendor_invoice_number }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="text-muted"><i class="mdi mdi-paperclip me-1"></i> Invoice Picture:</span>
                                        <div>
                                            @if ($purchaseOrder->invoice_document)
                                                <a href="{{ $purchaseOrder->invoice_document_url }}" target="_blank"
                                                    class="badge bg-primary text-decoration-none px-2 py-1">
                                                    <i class="mdi mdi-eye me-1"></i>View Picture
                                                </a>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-muted"
                                                    data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal" title="Upload or Replace invoice picture">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2"
                                                    style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal">
                                                    <i class="mdi mdi-camera me-1"></i>Attach Picture
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Info --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 position-relative rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.5px;">
                                            Receiving Progress</h6>
                                        @php
                                            $badges = [
                                                'ordered' => 'info',
                                                'partial' => 'warning',
                                                'completed' => 'success',
                                            ];
                                            $displayStatus = strtoupper($purchaseOrder->status);
                                            if ($purchaseOrder->status === 'partial') {
                                                $displayStatus = 'IN TRANSIT';
                                            }
                                        @endphp
                                        <h5 class="fw-bold text-dark mb-0">
                                            <span
                                                class="badge bg-{{ $badges[$purchaseOrder->status] ?? 'secondary' }} rounded-pill px-3 py-1">
                                                {{ $displayStatus }}
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-truck-delivery fs-4"></i>
                                    </div>
                                </div>
                                <hr class="text-muted opacity-25 my-3">
                                <div class="mt-2 text-dark">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold">Progress</span>
                                        @if ($purchaseOrder->progress < 100 && $purchaseOrder->status === 'completed')
                                            <span class="small fw-bold text-primary">PARTIAL COMPLETED</span>
                                        @else
                                            <span class="small fw-bold text-primary">{{ $purchaseOrder->progress }}%</span>
                                        @endif
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $purchaseOrder->progress }}%;"
                                            aria-valuenow="{{ $purchaseOrder->progress }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('warehouse.purchase-orders.partials.over-receipt-panel')

                {{-- RECEIVE SECTION --}}
                @if (auth()->user()->can('receive_po'))
                    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-primary mb-0"><i class="mdi mdi-truck-check me-2"></i> Receive Incoming
                                Stock</h5>
                        </div>
                        <div class="card-body p-4">
                            {{-- SCANNER INPUT --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white border-primary">
                                            <i class="mdi mdi-barcode-scan"></i>
                                        </span>
                                        <input type="text" id="scannerInput" class="form-control border-primary"
                                            placeholder="Scan Barcode / UPC (Supports Long UPC codes)...">
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="mdi mdi-information-outline me-1"></i>
                                        Scan item to automatically find and focus it in the list.</small>
                                </div>
                            </div>

                            <form action="{{ route('warehouse.purchase-orders.receive', $purchaseOrder->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- Client PDF 9/24, items 2-3: what happens if less arrives than ordered --}}
                                <div class="mb-4 p-3 border rounded bg-light">
                                    <label class="form-label fw-semibold d-block">Shipment Type <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shipment_type" id="shipTruck" value="truck" required
                                                {{ old('shipment_type', $purchaseOrder->shipment_type) === 'truck' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="shipTruck"><i class="mdi mdi-truck"></i> <strong>Truck</strong>
                                                <small class="text-muted d-block">If less arrives than ordered, the order is closed.</small></label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shipment_type" id="shipContainer" value="container" required
                                                {{ old('shipment_type', $purchaseOrder->shipment_type) === 'container' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="shipContainer"><i class="mdi mdi-ferry"></i> <strong>Container</strong>
                                                <small class="text-muted d-block">If less arrives than ordered, the order stays open (In Transit).</small></label>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">Receiving more than ordered is allowed; the order is then flagged and sent to the approver.</small>
                                    @error('shipment_type')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Vendor Invoice Number <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-receipt"></i></span>
                                            <input type="text" name="invoice_number" class="form-control" required
                                                placeholder="e.g. INV-9988" value="{{ $purchaseOrder->vendor_invoice_number ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Duties ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i
                                                    class="mdi mdi-currency-usd"></i></span>
                                            <input type="number" name="duties" class="form-control cost-input" step="0.01"
                                                min="0" value="{{ $purchaseOrder->duties ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Shipping Cost ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i
                                                    class="mdi mdi-truck-delivery"></i></span>
                                            <input type="number" name="shipping_cost" class="form-control cost-input"
                                                step="0.01" min="0" value="{{ $purchaseOrder->shipping_cost ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Taxes/Brokerage Fee (Flat Rate) ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-percent"></i></span>
                                            <input type="number" name="taxes" class="form-control cost-input" step="0.01"
                                                min="0" value="{{ $purchaseOrder->taxes ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Transportation Cost ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-truck"></i></span>
                                            <input type="number" name="transportation_cost" class="form-control cost-input" step="0.01"
                                                min="0" value="{{ $purchaseOrder->transportation_cost ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Demurrage ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-clock-alert"></i></span>
                                            <input type="number" name="demurrage" class="form-control cost-input" step="0.01"
                                                min="0" value="{{ $purchaseOrder->demurrage ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Vendor Invoice Picture / Attachment
                                            @if ($purchaseOrder->invoice_document)
                                                <span class="badge bg-success bg-opacity-10 text-success ms-1">
                                                    <i class="mdi mdi-check-circle"></i> Attached
                                                </span>
                                            @endif
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-camera"></i></span>
                                            <input type="file" name="invoice_document" id="invoiceDocumentInput" class="form-control"
                                                accept="image/*,application/pdf" onchange="previewInvoiceFile(this)">
                                            @if ($purchaseOrder->invoice_document)
                                                <a href="{{ $purchaseOrder->invoice_document_url }}" target="_blank"
                                                    class="btn btn-outline-info" title="View Current Attached Invoice">
                                                    <i class="mdi mdi-eye"></i> View Current
                                                </a>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block mt-1">Upload a photo or scanned copy of the vendor invoice (JPG, PNG, WebP, or PDF, max 10MB).</small>

                                        {{-- Image Preview Container --}}
                                        <div id="invoiceImagePreviewContainer" class="mt-2 d-none">
                                            <div class="position-relative d-inline-block border rounded p-1 bg-light shadow-sm">
                                                <img id="invoiceImagePreview" src="" alt="Invoice Preview"
                                                    style="max-height: 120px; max-width: 260px; object-fit: contain;" class="rounded">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle"
                                                    style="width: 22px; height: 22px; line-height: 20px;" onclick="clearInvoicePreview()" title="Remove file">
                                                    &times;
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead class="bg-light text-uppercase small text-muted">
                                            <tr>
                                                <th class="px-3">UPC</th>
                                                <th class="px-3">Product</th>
                                                <th class="text-center" style="min-width: 110px;">Ordered Qty</th>
                                                <th class="text-center">PO Price ($)</th>
                                                <th style="min-width: 130px;">Receive Qty</th>
                                                <th style="min-width: 130px;">Receiving Price ($)</th>
                                                <th style="min-width: 120px;">Current Cost ($)</th>
                                                <th style="min-width: 120px;">True Cost ($)</th>
                                                <th style="min-width: 150px;">Batch No.</th>
                                                <th style="min-width: 140px;">Mfg Date</th>
                                                <th style="min-width: 140px;">Expiry Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $hasPendingItems = false; @endphp
                                            @foreach ($purchaseOrder->items as $item)
                                                @if ($item->pending_quantity > 0)
                                                    @php $hasPendingItems = true; @endphp
                                                    <tr>
                                                        <td class="px-3">
                                                            <span
                                                                class="badge bg-secondary">{{ $item->product->upc ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="px-3">
                                                            <span
                                                                class="fw-semibold text-dark">{{ $item->product->product_name }}</span><br>
                                                            <small class="text-muted">
                                                                UPC: {{ $item->product->upc }}
                                                                @if ($item->product->plu_code)
                                                                    | PLU: {{ $item->product->plu_code }}
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number"
                                                                name="items[{{ $item->id }}][ordered_qty]"
                                                                class="form-control form-control-sm text-center fw-medium ordered-qty-input"
                                                                min="0" step="0.01" value="{{ $item->requested_quantity }}">
                                                            <small class="text-muted d-block">Edit if the shipment
                                                                differs from what was ordered</small>
                                                        </td>
                                                        <td class="text-center fw-medium">$
                                                            {{ number_format($item->unit_cost, 2) }}</td>
                                                        <td class="text-center">
                                                            <input type="number"
                                                                name="items[{{ $item->id }}][receive_qty]"
                                                                class="form-control form-control-sm text-center fw-bold text-primary receive-qty-input"
                                                                min="0" step="0.01"
                                                                value="0">
                                                            <small class="text-muted">Pending:
                                                                <span class="pending-qty" data-pending="{{ $item->pending_quantity }}">{{ $item->pending_quantity }}</span>
                                                                <br>Can receive more or less than ordered.</small>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="items[{{ $item->id }}][receiving_price]"
                                                                class="form-control form-control-sm text-center fw-bold text-primary receiving-price-input"
                                                                step="0.01" min="0"
                                                                value="{{ $item->unit_cost }}">
                                                        </td>
                                                        <td class="text-center fw-medium text-muted">
                                                            {{ number_format($item->product->cost_price ?? 0, 2) }}
                                                        </td>
                                                        <td class="text-center fw-bold text-success true-cost-display">
                                                            {{ number_format($item->unit_cost, 2) }}
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="items[{{ $item->id }}][batch_number]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Auto if empty">
                                                        </td>
                                                        <td>
                                                            <input type="date"
                                                                name="items[{{ $item->id }}][mfg_date]"
                                                                class="form-control form-control-sm">
                                                        </td>
                                                        <td>
                                                            <input type="date"
                                                                name="items[{{ $item->id }}][expiry_date]"
                                                                class="form-control form-control-sm"
                                                                min="{{ date('Y-m-d') }}">
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            @if (!$hasPendingItems)
                                                <tr>
                                                    <td colspan="11" class="text-center py-4 text-success fw-bold">
                                                        <i class="mdi mdi-check-circle-outline fs-3 d-block mb-2"></i>
                                                        All items for this order have been fully received.
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                @if ($hasPendingItems)
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn btn-primary shadow-sm px-4">
                                            <i class="mdi mdi-check-all me-1"></i> Process Receive
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                        <i class="mdi mdi-lock-alert me-3 fs-4"></i>
                        <div>
                            <strong>Access Restricted:</strong> Only Inventory Managers can receive stock.
                        </div>
                    </div>
                @endif

                {{-- RECEIVED ITEMS LOG --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="mdi mdi-history me-2 text-primary"></i> Receiving
                            History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="px-4 py-3">Product</th>
                                        <th class="py-3 text-center">Received Qty</th>
                                        <th class="py-3">Batch No</th>
                                        <th class="py-3 text-end px-4">Cost Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasReceived = false; @endphp
                                    @foreach ($purchaseOrder->items as $item)
                                        @if ($item->received_quantity > 0)
                                            @php $hasReceived = true; @endphp
                                            <tr>
                                                <td class="px-4 text-dark">
                                                    <div class="d-flex align-items-center">
                                                        @if ($item->product->main_image)
                                                            <img src="{{ Storage::disk('r2')->url($item->product->main_image) }}"
                                                                alt="" class="rounded"
                                                                style="width: 40px; height: 40px; object-fit: cover; margin-right: 12px;">
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $item->product->product_name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-success text-center">
                                                    +{{ $item->received_quantity }}</td>
                                                <td><span
                                                        class="badge bg-light text-dark border">{{ $item->batch_number ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-end px-4 fw-medium">
                                                    ${{ number_format($item->received_quantity * $item->unit_cost, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    @if (!$hasReceived)
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No items have been received yet for this order.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- UPLOAD / REPLACE INVOICE MODAL --}}
    <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('warehouse.receiving.upload-invoice', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="uploadInvoiceModalLabel">
                            <i class="mdi mdi-receipt text-primary me-2"></i>
                            {{ $purchaseOrder->invoice_document ? 'Update Vendor Invoice Picture' : 'Attach Vendor Invoice Picture' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if ($purchaseOrder->invoice_document)
                            <div class="alert alert-light border mb-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-file-check text-success fs-3 me-2"></i>
                                    <div>
                                        <div class="fw-semibold small">Current Invoice Document Attached</div>
                                        <small class="text-muted">Uploading a new picture will replace the current file.</small>
                                    </div>
                                </div>
                                <a href="{{ $purchaseOrder->invoice_document_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-eye me-1"></i> View
                                </a>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Vendor Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control"
                                placeholder="e.g. INV-9988" value="{{ $purchaseOrder->vendor_invoice_number ?? '' }}">
                            <small class="text-muted">Update the invoice reference number if needed.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Choose Invoice Picture / Document <span class="text-danger">*</span></label>
                            <input type="file" name="invoice_document" class="form-control" required
                                accept="image/*,application/pdf" onchange="previewModalInvoice(this)">
                            <small class="text-muted">Supports photos (JPG, PNG, WebP) or PDF documents up to 10MB.</small>
                        </div>

                        <div id="modalInvoicePreviewContainer" class="text-center d-none mt-3 p-2 bg-light rounded border">
                            <img id="modalInvoicePreview" src="" alt="Preview" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-upload me-1"></i> Save Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // True Cost Calculation Logic
            function calculateTrueCost() {
                // Get all overhead costs
                let duties = parseFloat(document.querySelector('input[name="duties"]').value) || 0;
                let shipping = parseFloat(document.querySelector('input[name="shipping_cost"]').value) || 0;
                let taxes = parseFloat(document.querySelector('input[name="taxes"]').value) || 0;
                let transport = parseFloat(document.querySelector('input[name="transportation_cost"]').value) || 0;
                let demurrage = parseFloat(document.querySelector('input[name="demurrage"]').value) || 0;

                let totalOverhead = duties + shipping + taxes + transport + demurrage;

                // Get total receiving quantity
                let totalQty = 0;
                document.querySelectorAll('.receive-qty-input').forEach(input => {
                    totalQty += parseFloat(input.value) || 0;
                });

                let landedFeePerUnit = totalQty > 0 ? (totalOverhead / totalQty) : 0;

                // Update True Cost for each row
                document.querySelectorAll('.receive-qty-input').forEach(input => {
                    let row = input.closest('tr');
                    let priceInput = row.querySelector('.receiving-price-input');
                    if (priceInput) {
                        let poPrice = parseFloat(priceInput.value) || 0;
                        let trueCost = poPrice + landedFeePerUnit;
                        let trueCostDisplay = row.querySelector('.true-cost-display');
                        if (trueCostDisplay) {
                            // If receiving qty is 0, true cost might just be PO price for display
                            let qty = parseFloat(input.value) || 0;
                            if(qty === 0) {
                                trueCostDisplay.innerText = poPrice.toFixed(2);
                            } else {
                                trueCostDisplay.innerText = trueCost.toFixed(2);
                            }
                        }
                    }
                });
            }

            // Attach event listeners to all related inputs
            document.querySelectorAll('.cost-input, .receive-qty-input, .receiving-price-input').forEach(input => {
                input.addEventListener('input', calculateTrueCost);
            });
            
            // Initial calculation
            calculateTrueCost();

            // Scanner Logic
            const scannerInput = document.getElementById('scannerInput');
            if (scannerInput) {
                scannerInput.focus();
                scannerInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const barcode = this.value.trim();
                        if (!barcode) return;

                        let found = false;
                        document.querySelectorAll('.receive-qty-input').closest('tr').forEach(row => {
                            const upcText = row.querySelector('small.text-muted')?.innerText || '';
                            if (upcText.includes(barcode)) {
                                found = true;
                                // Highlight row
                                row.classList.add('table-primary');
                                setTimeout(() => row.classList.remove('table-primary'), 2000);

                                // Scroll & Focus
                                row.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                const qtyInput = row.querySelector('.receive-qty-input');
                                if (qtyInput) {
                                    qtyInput.focus();
                                    qtyInput.select();
                                }
                            }
                        });

                        if (found) {
                            this.value = '';
                        }
                    }
                });
            }

            const receiveAllBtn = document.getElementById('receiveAllBtn');
            if (receiveAllBtn) {
                receiveAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('tr').forEach(row => {
                        const pendingEl = row.querySelector('.pending-qty');
                        const receiveInput = row.querySelector('.receive-qty-input');
                        if (pendingEl && receiveInput) {
                            const pendingVal = pendingEl.getAttribute('data-pending');
                            receiveInput.value = pendingVal;
                        }
                    });
                });
            }
        });

        window.previewInvoiceFile = function(input) {
            const container = document.getElementById('invoiceImagePreviewContainer');
            const img = document.getElementById('invoiceImagePreview');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        container.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    container.classList.add('d-none');
                }
            } else {
                container.classList.add('d-none');
            }
        };

        window.clearInvoicePreview = function() {
            const input = document.getElementById('invoiceDocumentInput');
            const container = document.getElementById('invoiceImagePreviewContainer');
            const img = document.getElementById('invoiceImagePreview');
            if (input) input.value = '';
            if (img) img.src = '';
            if (container) container.classList.add('d-none');
        };

        window.previewModalInvoice = function(input) {
            const container = document.getElementById('modalInvoicePreviewContainer');
            const img = document.getElementById('modalInvoicePreview');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        container.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    container.classList.add('d-none');
                }
            } else {
                container.classList.add('d-none');
            }
        };
    </script>
@endpush
