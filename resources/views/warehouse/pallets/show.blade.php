<x-app-layout title="Pallet Builder — {{ $pallet->pallet_number }}">
    <div class="container-fluid p-3 p-md-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('warehouse.pallets.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-0">
                        <i class="mdi mdi-dolly text-primary me-2"></i>{{ $pallet->pallet_number }}
                    </h4>
                    <small class="text-muted">
                        {{ $pallet->department->name ?? 'No Department' }}
                        @if($pallet->storePO) &bull; Linked to PO: <strong>{{ $pallet->storePO->po_number }}</strong> ({{ $pallet->storePO->store->store_name ?? '' }}) @endif
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('warehouse.pallets.manifest', $pallet->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-printer me-1"></i> Print Manifest
                </a>
                @if($pallet->status === 'preparing')
                    <form action="{{ route('warehouse.pallets.mark-ready', $pallet->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" {{ $pallet->items->isEmpty() ? 'disabled' : '' }}>
                            <i class="mdi mdi-check-circle me-1"></i> Mark as Ready
                        </button>
                    </form>
                @else
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="mdi mdi-check-circle me-1"></i> {{ ucfirst(str_replace('_', ' ', $pallet->status)) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="row g-4">

            {{-- LEFT: Weight Gauge + Items List --}}
            <div class="col-12 col-lg-7">

                {{-- Weight Gauge --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Pallet Weight</span>
                            <span class="fw-bold fs-5 {{ $weightPercent >= 90 ? 'text-danger' : ($weightPercent >= 70 ? 'text-warning' : 'text-success') }}">
                                <span id="totalWeight">{{ number_format($pallet->total_weight, 2) }}</span> / {{ number_format($pallet->max_weight, 0) }} lbs
                            </span>
                        </div>
                        <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar bg-{{ $weightPercent >= 90 ? 'danger' : ($weightPercent >= 70 ? 'warning' : 'success') }} fw-bold"
                                 id="weightBar"
                                 style="width: {{ $weightPercent }}%; border-radius: 10px;"
                                 role="progressbar">
                                {{ $weightPercent }}%
                            </div>
                        </div>
                        <div class="small text-muted">
                            Remaining capacity: <strong id="remainingWeight">{{ number_format($pallet->remainingCapacity(), 2) }}</strong> lbs
                        </div>
                        @if($pallet->isOverweight())
                            <div class="alert alert-danger mt-2 mb-0 py-2">
                                <i class="mdi mdi-alert me-1"></i> <strong>Overweight!</strong> This pallet exceeds the 2,200 lb limit.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Items on Pallet --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="mdi mdi-package-variant me-2"></i> Items on Pallet</h6>
                    </div>
                    <div class="card-body p-0" id="palletItemsList">
                        @if($pallet->items->isEmpty())
                            <div class="text-center py-5 text-muted" id="emptyState">
                                <i class="mdi mdi-package-variant-closed-remove fs-1 opacity-25 d-block mb-2"></i>
                                No items added yet. Use the form on the right to add products.
                            </div>
                        @else
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-2">Product</th>
                                        <th class="py-2 text-center">Qty</th>
                                        <th class="py-2 text-center">Wt/Unit</th>
                                        <th class="py-2 text-end">Total Wt</th>
                                        @if($pallet->status === 'preparing')
                                            <th class="py-2"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    @foreach($pallet->items as $item)
                                        <tr id="item-row-{{ $item->id }}">
                                            <td class="px-4 py-2 fw-semibold">{{ $item->product->product_name ?? 'N/A' }}</td>
                                            <td class="py-2 text-center">{{ $item->quantity }}</td>
                                            <td class="py-2 text-center">{{ $item->weight_per_unit }} lbs</td>
                                            <td class="py-2 text-end fw-bold">{{ number_format($item->total_weight, 2) }} lbs</td>
                                            @if($pallet->status === 'preparing')
                                                <td class="py-2 text-end px-3">
                                                    <button class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="{{ $item->id }}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT: Add Item Form --}}
            @if($pallet->status === 'preparing')
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="mdi mdi-plus-circle text-success me-2"></i> Add Item to Pallet</h6>
                    </div>
                    <div class="card-body">
                        <div id="addItemAlert"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Product</label>
                            <select id="productSelect" class="form-select">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-weight="{{ $product->box_weight ?? 0 }}"
                                            data-stock="{{ $product->stock->quantity ?? 0 }}">
                                        {{ $product->product_name }}
                                        (Stock: {{ $product->stock->quantity ?? 0 }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quantity</label>
                            <input type="number" id="itemQty" class="form-control" min="1" placeholder="e.g. 10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Weight per Unit (lbs)</label>
                            <input type="number" id="itemWeight" class="form-control" min="0.01" step="0.01" placeholder="e.g. 22">
                            <div class="form-text" id="weightPreview"></div>
                        </div>

                        <button type="button" id="addItemBtn" class="btn btn-success w-100">
                            <i class="mdi mdi-plus me-1"></i> Add to Pallet
                        </button>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        const palletId = {{ $pallet->id }};
        const maxWeight = {{ $pallet->max_weight }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Auto-fill weight from product selection
        document.getElementById('productSelect')?.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const weight = opt.dataset.weight || '';
            document.getElementById('itemWeight').value = weight;
            updateWeightPreview();
        });

        document.getElementById('itemQty')?.addEventListener('input', updateWeightPreview);
        document.getElementById('itemWeight')?.addEventListener('input', updateWeightPreview);

        function updateWeightPreview() {
            const qty = parseFloat(document.getElementById('itemQty').value) || 0;
            const wt = parseFloat(document.getElementById('itemWeight').value) || 0;
            const total = qty * wt;
            const currentWeight = parseFloat(document.getElementById('totalWeight').textContent.replace(',', ''));
            const remaining = maxWeight - currentWeight;
            const preview = document.getElementById('weightPreview');

            if (total > 0) {
                if (total > remaining) {
                    preview.innerHTML = `<span class="text-danger">⚠ This would add ${total.toFixed(2)} lbs — exceeds remaining ${remaining.toFixed(2)} lbs</span>`;
                } else {
                    preview.innerHTML = `This will add <strong>${total.toFixed(2)} lbs</strong>`;
                }
            } else {
                preview.innerHTML = '';
            }
        }

        // Add Item
        document.getElementById('addItemBtn')?.addEventListener('click', function() {
            const productId = document.getElementById('productSelect').value;
            const qty = document.getElementById('itemQty').value;
            const weight = document.getElementById('itemWeight').value;
            const alertDiv = document.getElementById('addItemAlert');

            if (!productId || !qty || !weight) {
                alertDiv.innerHTML = '<div class="alert alert-warning py-2">Please fill all fields.</div>';
                return;
            }

            fetch(`/warehouse/pallets/${palletId}/items`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ product_id: productId, quantity: qty, weight_per_unit: weight })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alertDiv.innerHTML = `<div class="alert alert-success py-2">${data.message}</div>`;
                    updateWeightDisplay(data.total_weight, data.remaining, data.weight_percent);
                    location.reload(); // Reload to show new item in table
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`;
                }
            })
            .catch(() => {
                alertDiv.innerHTML = '<div class="alert alert-danger py-2">An error occurred. Please try again.</div>';
            });
        });

        // Remove Item
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.itemId;
                if (!confirm('Remove this item from the pallet?')) return;

                fetch(`/warehouse/pallets/items/${itemId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`item-row-${itemId}`)?.remove();
                        updateWeightDisplay(data.total_weight, data.remaining, data.weight_percent);
                    }
                });
            });
        });

        function updateWeightDisplay(totalWeight, remaining, pct) {
            document.getElementById('totalWeight').textContent = totalWeight;
            document.getElementById('remainingWeight').textContent = remaining;
            const bar = document.getElementById('weightBar');
            bar.style.width = pct + '%';
            bar.textContent = pct + '%';
        }
    </script>
    @endpush
</x-app-layout>
