<x-app-layout title="Warehouse | Inventory System">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    
                    {{-- LEFT: BREADCRUMB & TITLE --}}
                    <div class="d-flex flex-column gap-2">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="mdi mdi-home-outline"></i> Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Warehouse
                                </li>
                            </ol>
                        </nav>
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-warehouse text-primary"></i>
                            Warehouse Management
                        </h4>
                    </div>

                    {{-- RIGHT: SEARCH & ACTIONS --}}
                    <div class="flex-shrink-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- SEARCH + FILTER --}}
                            <form method="GET" class="d-flex flex-grow-1" style="max-width: 500px;">
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           class="form-control border-end-0" 
                                           placeholder="Search warehouse...">

                                    <select name="status" class="form-select border-start-0 border-end-0">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>

                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>

                                    @if (request('search') || request('status') !== null)
                                        <a href="{{ route('warehouse.index') }}" 
                                           class="btn btn-outline-secondary" 
                                           title="Clear Filters">
                                            <i class="mdi mdi-close"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>

                            {{-- ADD BUTTON --}}
                            {{-- <a href="{{ route('warehouse.create') }}" 
                               class="btn btn-success d-flex align-items-center gap-1 ms-2">
                                <i class="mdi mdi-plus-circle"></i>
                                <span class="d-none d-lg-inline">Add Warehouse</span>
                            </a> --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-warehouse text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Total Warehouses</p>
                                <h4 class="mb-0 fw-bold">{{ $warehouses->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Active</p>
                                <h4 class="mb-0 fw-bold text-success">{{ $warehouses->where('is_active', 1)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-close-circle text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Inactive</p>
                                <h4 class="mb-0 fw-bold text-warning">{{ $warehouses->where('is_active', 0)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i>
                    Warehouse List
                </h5>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">#</th>
                                <th class="py-3 text-muted fw-semibold small">WAREHOUSE NAME</th>
                                <th class="py-3 text-muted fw-semibold small">CODE</th>
                                <th class="py-3 text-muted fw-semibold small">CONTACT</th>
                                <th class="py-3 text-muted fw-semibold small text-center">STATUS</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($warehouses as $warehouse)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">
                                        {{ $loop->iteration + ($warehouses->currentPage()-1)*$warehouses->perPage() }}
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2">
                                                <i class="mdi mdi-warehouse text-primary"></i>
                                            </div>
                                            <span class="fw-semibold">{{ $warehouse->warehouse_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <code class="bg-light px-2 py-1 rounded">{{ $warehouse->code }}</code>
                                    </td>
                                    <td class="py-3">
                                        @if($warehouse->email || $warehouse->phone)
                                            <div class="d-flex flex-column gap-1">
                                                @if($warehouse->email)
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-email-outline"></i> {{ $warehouse->email }}
                                                    </small>
                                                @endif
                                                @if($warehouse->phone)
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-phone-outline"></i> {{ $warehouse->phone }}
                                                    </small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" 
                                                   type="checkbox"
                                                   role="switch"
                                                   data-id="{{ $warehouse->id }}"
                                                   {{ $warehouse->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('warehouse.edit', $warehouse) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Edit Warehouse">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="mdi mdi-warehouse text-muted" style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No warehouse found.</p>
                                            {{-- <a href="{{ route('warehouse.create') }}" 
                                               class="btn btn-sm btn-primary mt-3">
                                                <i class="mdi mdi-plus"></i> Add First Warehouse
                                            </a> --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($warehouses->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            Showing {{ $warehouses->firstItem() }} to {{ $warehouses->lastItem() }} of {{ $warehouses->total() }} entries
                        </div>
                        <div>
                            {{ $warehouses->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    @push('scripts')
        <script>
            $('.status-toggle').change(function() {

                let checkbox = $(this);
                let id = checkbox.data('id');
                let status = checkbox.is(':checked') ? 1 : 0;

                checkbox.prop('checked', !checkbox.is(':checked'));

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Change warehouse status?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        checkbox.prop('checked', !checkbox.prop('checked'));

                        $.post("{{ route('warehouse.update-status') }}", {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        }, function(res) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                icon: 'success',
                                title: res.message ?? 'Status updated successfully'
                            });
                        }).fail(function() {
                            checkbox.prop('checked', !checkbox.prop('checked'));
                            
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                icon: 'error',
                                title: 'Something went wrong!'
                            });
                        });
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>