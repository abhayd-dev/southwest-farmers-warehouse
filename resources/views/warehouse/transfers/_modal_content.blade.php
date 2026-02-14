<div class="p-4">
    {{-- Header Info --}}
    <div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded shadow-sm">
        <div class="text-start">
            <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.7rem;">From Source</small>
            <span class="fw-bold text-dark fs-6">{{ $transfer->fromStore->store_name }}</span>
        </div>
        
        <div class="text-center px-3">
            <div class="avatar-sm bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mx-auto">
                <i class="mdi mdi-arrow-right text-primary fs-4"></i>
            </div>
            @php
                $badges = [
                    'pending' => 'warning',
                    'in_transit' => 'info',
                    'completed' => 'success',
                    'rejected' => 'danger'
                ];
                $statusColor = $badges[$transfer->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $statusColor }} mt-2">{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</span>
        </div>

        <div class="text-end">
            <small class="text-muted text-uppercase fw-bold d-block" style="font-size: 0.7rem;">To Destination</small>
            <span class="fw-bold text-dark fs-6">{{ $transfer->toStore->store_name }}</span>
        </div>
    </div>

    {{-- Transfer Details Title --}}
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h6 class="fw-bold text-dark mb-0">
            <i class="mdi mdi-cube-outline me-1"></i> Transfer Content
        </h6>
        <span class="badge bg-light text-dark border">#{{ $transfer->transfer_number }}</span>
    </div>

    {{-- Single Product Table --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="bg-light text-uppercase small text-muted">
                <tr>
                    <th class="ps-3">Product Name</th>
                    <th>SKU</th>
                    <th class="text-center">Transferred Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ps-3 fw-semibold text-dark">
                        {{ $transfer->product->product_name ?? 'Unknown Product' }}
                    </td>
                    <td class="text-muted">
                        {{ $transfer->product->sku ?? '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary fs-6">
                            {{ $transfer->quantity }} {{ $transfer->product->unit ?? 'Units' }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Footer Info --}}
    <div class="mt-4 p-3 bg-light rounded border border-light">
        <div class="d-flex align-items-start gap-2">
            <i class="mdi mdi-information-outline text-info mt-1"></i>
            <div class="small text-muted">
                <div><strong>Created On:</strong> {{ $transfer->created_at->format('d M Y, h:i A') }}</div>
                @if($transfer->notes)
                    <div class="mt-1"><strong>Note:</strong> {{ $transfer->notes }}</div>
                @endif
                @if($transfer->creator)
                    <div class="mt-1"><strong>Initiated By:</strong> {{ $transfer->creator->name }}</div>
                @endif
            </div>
        </div>
    </div>
</div>