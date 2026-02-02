<x-app-layout title="Audit: {{ $audit->audit_number }}">
    <div class="container-fluid">
        
        {{-- Header Info --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">{{ $audit->audit_number }}</h4>
                <div class="d-flex align-items-center gap-3 text-muted small">
                    <span><i class="mdi mdi-calendar me-1"></i> {{ $audit->created_at->format('d M Y, h:i A') }}</span>
                    <span><i class="mdi mdi-account me-1"></i> {{ $audit->initiator->name ?? 'System' }}</span>
                    <span class="badge bg-{{ $audit->status == 'completed' ? 'success' : 'warning' }}">
                        {{ ucfirst(str_replace('_', ' ', $audit->status)) }}
                    </span>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('warehouse.stock-control.audit.index') }}" class="btn btn-light border shadow-sm">Back</a>
                
                @if($audit->status != 'completed')
                    {{-- Save Draft Button --}}
                    <button type="submit" form="auditForm" class="btn btn-primary shadow-sm">
                        <i class="mdi mdi-content-save me-1"></i> Save Progress
                    </button>

                    {{-- Finalize Button --}}
                    <form action="{{ route('warehouse.stock-control.audit.finalize', $audit->id) }}" method="POST" class="d-inline" id="finalizeForm">
                        @csrf
                        <button type="button" onclick="confirmFinalize()" class="btn btn-success shadow-sm text-white">
                            <i class="mdi mdi-check-circle me-1"></i> Finalize & Adjust Stock
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Counting Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <form action="{{ route('warehouse.stock-control.audit.update-counts', $audit->id) }}" method="POST" id="auditForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light text-uppercase small">
                                <tr>
                                    <th class="ps-4">Product Name</th>
                                    <th>SKU</th>
                                    <th class="text-center bg-info bg-opacity-10">System Qty (Frozen)</th>
                                    <th class="text-center" width="150">Physical Count</th>
                                    <th class="text-center">Variance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($audit->items as $item)
                                @php
                                    $variance = $item->physical_qty !== null ? ($item->physical_qty - $item->system_qty) : 0;
                                    $varianceClass = $variance == 0 ? 'text-muted' : ($variance < 0 ? 'text-danger fw-bold' : 'text-success fw-bold');
                                    $varianceIcon = $variance == 0 ? 'mdi-check' : ($variance < 0 ? 'mdi-arrow-down' : 'mdi-arrow-up');
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $item->product->product_name }}</td>
                                    <td class="text-muted small">{{ $item->product->sku }}</td>
                                    
                                    {{-- System Qty --}}
                                    <td class="text-center bg-info bg-opacity-10 fw-bold">
                                        {{ number_format($item->system_qty) }}
                                    </td>

                                    {{-- Input Physical Qty --}}
                                    <td class="p-2">
                                        <input type="number" step="1" 
                                            name="items[{{ $item->id }}]" 
                                            value="{{ $item->physical_qty !== null ? (int)$item->physical_qty : '' }}" 
                                            class="form-control text-center fw-bold physical-input"
                                            data-system="{{ $item->system_qty }}"
                                            placeholder="Enter Qty"
                                            {{ $audit->status == 'completed' ? 'disabled' : '' }}>
                                    </td>

                                    {{-- Variance (Live Calc) --}}
                                    <td class="text-center">
                                        <span class="variance-display {{ $varianceClass }}">
                                            @if($item->physical_qty !== null)
                                                <i class="mdi {{ $varianceIcon }}"></i> {{ $variance > 0 ? '+'.$variance : $variance }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // 1. Live Variance Calculation
        document.querySelectorAll('.physical-input').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const systemQty = parseFloat(this.dataset.system);
                const physicalQty = parseFloat(this.value);
                const display = row.querySelector('.variance-display');

                if (isNaN(physicalQty)) {
                    display.innerHTML = '-';
                    display.className = 'variance-display text-muted';
                    return;
                }

                const variance = physicalQty - systemQty;
                let html = '';
                let colorClass = '';

                if (variance === 0) {
                    html = '<i class="mdi mdi-check"></i> 0';
                    colorClass = 'text-muted';
                } else if (variance < 0) {
                    html = `<i class="mdi mdi-arrow-down"></i> ${variance}`;
                    colorClass = 'text-danger fw-bold';
                } else {
                    html = `<i class="mdi mdi-arrow-up"></i> +${variance}`;
                    colorClass = 'text-success fw-bold';
                }

                display.innerHTML = html;
                display.className = `variance-display ${colorClass}`;
            });
        });

        // 2. Finalize Confirmation
        function confirmFinalize() {
            Swal.fire({
                title: 'Finalize Audit?',
                text: "This will permanently adjust the warehouse inventory to match the physical counts. This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Adjust Inventory'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('finalizeForm').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>