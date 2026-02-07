<div class="p-4">
    {{-- Header Info --}}
    <div class="d-flex justify-content-between mb-4 bg-light p-3 rounded">
        <div>
            <small class="text-muted d-block">FROM</small>
            <span class="fw-bold">{{ $transfer->fromStore->store_name }}</span>
        </div>
        <div class="text-center">
            <i class="mdi mdi-arrow-right-bold-circle text-primary fs-3"></i>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">TO</small>
            <span class="fw-bold">{{ $transfer->toStore->store_name }}</span>
        </div>
    </div>

    {{-- Items Table --}}
    <h6 class="fw-bold border-bottom pb-2 mb-3">Items included in Transfer #{{ $transfer->transfer_number }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-center">Sent Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->items as $item)
                <tr>
                    <td>{{ $item->product->product_name }}</td>
                    <td><small class="text-muted">{{ $item->product->sku }}</small></td>
                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer Info --}}
    <div class="mt-3 text-muted small">
        <i class="mdi mdi-information-outline me-1"></i>
        Created on: {{ $transfer->created_at->format('d M Y h:i A') }}
        @if($transfer->notes)
            <br> Note: {{ $transfer->notes }}
        @endif
    </div>
</div>