<x-app-layout title="Warehouse | Inventory System">

    {{-- LEAFLET CSS --}}
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            #viewMap { height: 100%; min-height: 300px; width: 100%; border-radius: 8px; }
            .detail-label { font-size: 0.85rem; color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
            .detail-value { font-size: 1rem; color: #212529; font-weight: 500; }
            .modal-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        </style>
    @endpush

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
                                <p class="text-muted mb-1 small ">Total Warehouses</p>
                                <h4 class="mb-0 fw-bold stats-number-red" >{{ $warehouses->total() }}</h4>
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
                                        {{-- SHOW BUTTON (Added) --}}
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info me-1 view-warehouse-btn"
                                                data-warehouse="{{ json_encode($warehouse) }}"
                                                data-bs-toggle="tooltip" 
                                                title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </button>

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

    {{-- VIEW DETAILS MODAL --}}
    <div class="modal fade" id="viewWarehouseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                
                {{-- Modal Header --}}
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                            <i class="mdi mdi-office-building text-info fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="viewModalTitle">Warehouse Details</h5>
                            <span class="badge bg-success mt-1" id="viewModalStatus">Active</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- LEFT: Text Details --}}
                        <div class="col-lg-5 border-end">
                            <div class="p-4">
                                <h6 class="fw-bold text-primary mb-4">
                                    <i class="mdi mdi-information-outline"></i> General Information
                                </h6>

                                <div class="mb-3">
                                    <div class="detail-label">Warehouse Name</div>
                                    <div class="detail-value" id="viewName">--</div>
                                </div>

                                <div class="mb-3">
                                    <div class="detail-label">Warehouse Code</div>
                                    <div class="detail-value text-monospace" id="viewCode">--</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="detail-label">Contact Details</div>
                                        <div class="d-flex align-items-center mt-1">
                                            <i class="mdi mdi-email-outline text-muted me-2"></i>
                                            <span id="viewEmail">--</span>
                                        </div>
                                        <div class="d-flex align-items-center mt-1">
                                            <i class="mdi mdi-phone-outline text-muted me-2"></i>
                                            <span id="viewPhone">--</span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="text-muted opacity-25">

                                <div>
                                    <div class="detail-label mb-1">Full Address</div>
                                    <p class="text-muted mb-0 small" id="viewAddress">--</p>
                                    <div class="mt-2 small text-muted">
                                        <span id="viewCity"></span>, <span id="viewState"></span> - <span id="viewPincode"></span>
                                        <div id="viewCountry" class="fw-semibold"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: Map --}}
                        <div class="col-lg-7 bg-light">
                            <div class="p-4 h-100 d-flex flex-column">
                                <h6 class="fw-bold text-danger mb-3">
                                    <i class="mdi mdi-map-marker-radius"></i> Location Map
                                </h6>
                                <div class="flex-grow-1 bg-white border rounded shadow-sm p-1 position-relative">
                                    <div id="viewMap"></div>
                                    <div id="mapPlaceholder" class="position-absolute top-50 start-50 translate-middle text-center w-100 p-3 d-none">
                                        <i class="mdi mdi-map-marker-off text-muted fs-1 opacity-50"></i>
                                        <p class="text-muted small mt-2">No location coordinates available.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        
        <script>
            $(document).ready(function() {
                
                // --- STATUS TOGGLE LOGIC (Existing) ---
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
                                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 })
                                    .fire({ icon: 'success', title: res.message ?? 'Status updated' });
                            }).fail(function() {
                                checkbox.prop('checked', !checkbox.prop('checked'));
                                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 })
                                    .fire({ icon: 'error', title: 'Something went wrong!' });
                            });
                        }
                    });
                });

                // --- SHOW MODAL LOGIC (New) ---
                let viewMap = null;
                let viewMarker = null;

                $('.view-warehouse-btn').on('click', function() {
                    const data = $(this).data('warehouse');
                    
                    // 1. Populate Text Fields
                    $('#viewModalTitle').text(data.warehouse_name);
                    $('#viewName').text(data.warehouse_name || '-');
                    $('#viewCode').text(data.code || '-');
                    $('#viewEmail').text(data.email || '-');
                    $('#viewPhone').text(data.phone || '-');
                    $('#viewAddress').text(data.address || 'No address provided');
                    $('#viewCity').text(data.city || '');
                    $('#viewState').text(data.state || '');
                    $('#viewPincode').text(data.pincode || '');
                    $('#viewCountry').text(data.country || '');

                    // Status Badge
                    const badge = $('#viewModalStatus');
                    if(data.is_active == 1) {
                        badge.removeClass('bg-secondary').addClass('bg-success').text('Active');
                    } else {
                        badge.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                    }

                    // 2. Handle Map
                    const lat = parseFloat(data.latitude);
                    const lng = parseFloat(data.longitude);
                    const hasCoords = !isNaN(lat) && !isNaN(lng);

                    if (hasCoords) {
                        $('#viewMap').show();
                        $('#mapPlaceholder').addClass('d-none');
                        
                        // Initialize Map if not exists
                        if (!viewMap) {
                            viewMap = L.map('viewMap');
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap'
                            }).addTo(viewMap);
                        }
                        
                        // Update View
                        viewMap.setView([lat, lng], 14);
                        
                        // Update Marker
                        if (viewMarker) viewMap.removeLayer(viewMarker);
                        viewMarker = L.marker([lat, lng]).addTo(viewMap)
                            .bindPopup(`<b>${data.warehouse_name}</b><br>${data.city || 'Location'}`)
                            .openPopup();
                    } else {
                        $('#viewMap').hide();
                        $('#mapPlaceholder').removeClass('d-none');
                    }

                    // 3. Show Modal
                    const modal = new bootstrap.Modal(document.getElementById('viewWarehouseModal'));
                    modal.show();

                    // 4. Fix Map Resize Issue in Modal
                    setTimeout(() => {
                        if(viewMap && hasCoords) viewMap.invalidateSize();
                    }, 500);
                });
            });
        </script>
    @endpush

</x-app-layout>