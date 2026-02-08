<x-app-layout title="Vendors">
    <div class="container-fluid">
        
        {{-- HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.vendors.partials.breadcrumb', ['title' => 'All Vendors'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-domain text-primary"></i> Vendor Management
                        </h4>
                    </div>
                    
                    {{-- ADD BUTTON (Protected) --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_vendors'))
                    <div class="flex-shrink-0">
                        <a href="{{ route('warehouse.vendors.create') }}" class="btn btn-success">
                            <i class="mdi mdi-plus-circle me-1"></i> Add New Vendor
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="vendors-table" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase">#</th>
                                <th class="py-3 small text-muted text-uppercase">Vendor Name</th>
                                <th class="py-3 small text-muted text-uppercase">Contact Info</th>
                                <th class="py-3 small text-muted text-uppercase text-center">On-Time %</th>
                                <th class="py-3 small text-muted text-uppercase">Rating</th>
                                <th class="py-3 small text-muted text-uppercase text-center">Status</th>
                                <th class="px-4 py-3 small text-muted text-uppercase text-end">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Define Toast Mixin
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            let table = $('#vendors-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.vendors.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 text-muted'},
                    {data: 'name', name: 'name', className: 'fw-semibold'},
                    {data: 'contact_info', name: 'contact_person'},
                    // On-Time %
                    {
                        data: 'on_time_delivery_rate', 
                        name: 'on_time_delivery_rate', 
                        className: 'text-center',
                        render: function(data) {
                            let val = data ? data : 0;
                            let color = val >= 90 ? 'success' : (val >= 70 ? 'warning' : 'danger');
                            return `<span class="badge bg-${color}">${val}%</span>`;
                        }
                    },
                    // Rating
                    {
                        data: 'rating', 
                        name: 'rating',
                        render: function(data) {
                            let stars = '';
                            let rating = Math.round(data ? data : 0);
                            for(let i=1; i<=5; i++) {
                                if(i <= rating) stars += '<i class="mdi mdi-star text-warning"></i>';
                                else stars += '<i class="mdi mdi-star-outline text-muted opacity-25"></i>';
                            }
                            return `<div class="d-flex align-items-center">${stars} <span class="ms-1 small text-muted">(${data || 0})</span></div>`;
                        }
                    },
                    {data: 'status', name: 'is_active', className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 text-end'}
                ],
                order: [[1, 'asc']],
                language: {
                    searchPlaceholder: "Search vendors..."
                }
            });

            // --- SweetAlert Confirmation Logic (Protected via backend too) ---
            $(document).on('change', '.status-toggle', function(e) {
                // ... (existing logic) ...
                let checkbox = $(this);
                let id = checkbox.data('id');
                let isChecked = checkbox.is(':checked');
                let newStatus = isChecked ? 1 : 0;
                let actionText = isChecked ? 'Activate' : 'Deactivate';

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to ${actionText.toLowerCase()} this vendor?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Yes, ${actionText}!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('warehouse.vendors.status') }}", {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: newStatus
                        }).done(function(res) {
                            Toast.fire({ icon: 'success', title: res.message });
                        }).fail(function() {
                            checkbox.prop('checked', !isChecked);
                            Toast.fire({ icon: 'error', title: 'Failed/Unauthorized' });
                        });
                    } else {
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>