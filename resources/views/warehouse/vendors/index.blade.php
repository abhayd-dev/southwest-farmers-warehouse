<x-app-layout title="Vendors">
    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2 ">
                        @include('warehouse.vendors.partials.breadcrumb', ['title' => 'All Vendors'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-domain text-primary"></i> Vendor Management
                        </h4>
                    </div>
                    
                    {{-- ADD BUTTON (Protected) --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_vendors'))
                    <div class=" d-flex align-items-end gap-2 ">
                        <a href="{{ route('warehouse.vendors.create') }}" class="btn btn-success w-40 w-md-auto shadow-sm">
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
                    <table id="vendors-table" class="table table-hover align-middle mb-0 text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold">#</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Vendor Name</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Contact Info</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">On-Time %</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">Rating</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">Status</th>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables populates this --}}
                        </tbody>
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

            // Initialize DataTable
            let table = $('#vendors-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.vendors.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 text-muted'},
                    {data: 'name', name: 'name', className: 'fw-semibold text-dark'},
                    {data: 'contact_info', name: 'contact_person'},
                    // On-Time %
                    {
                        data: 'on_time_delivery_rate', 
                        name: 'on_time_delivery_rate', 
                        className: 'text-center',
                        render: function(data, type, row) {
                            let val = data ? parseFloat(data) : 0;
                            let color = val >= 90 ? 'success' : (val >= 70 ? 'warning' : 'danger');
                            return `<span class="badge bg-${color}-subtle text-${color} border border-${color}-subtle">${val}%</span>`;
                        }
                    },
                    // Rating
                    {
                        data: 'rating', 
                        name: 'rating',
                        className: 'text-center',
                        render: function(data, type, row) {
                            let stars = '';
                            let rating = Math.round(data ? data : 0);
                            for(let i=1; i<=5; i++) {
                                if(i <= rating) stars += '<i class="mdi mdi-star text-warning"></i>';
                                else stars += '<i class="mdi mdi-star-outline text-muted opacity-25"></i>';
                            }
                            // Flex center for the stars
                            return `<div class="d-flex align-items-center justify-content-center gap-1">${stars} <span class="small text-muted">(${data || 0})</span></div>`;
                        }
                    },
                    {data: 'status', name: 'is_active', className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 text-end'}
                ],
                order: [[1, 'asc']],
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"f<"d-flex align-items-center gap-2"l>>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"ip>',
                language: {
                    search: "",
                    searchPlaceholder: "Search vendors...",
                    lengthMenu: "Show _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_ vendors",
                    paginate: {
                        next: '<i class="mdi mdi-chevron-right"></i>',
                        previous: '<i class="mdi mdi-chevron-left"></i>'
                    }
                }
            });

            // --- SweetAlert Confirmation Logic (Protected via backend too) ---
            $(document).on('change', '.status-toggle', function(e) {
                e.preventDefault();
                let checkbox = $(this);
                let id = checkbox.data('id');
                let isChecked = checkbox.is(':checked');
                let newStatus = isChecked ? 1 : 0;
                let actionText = isChecked ? 'Activate' : 'Deactivate';
                let confirmBtnColor = isChecked ? '#198754' : '#dc3545'; // Success Green or Danger Red

                Swal.fire({
                    title: `${actionText} Vendor?`,
                    text: `Are you sure you want to ${actionText.toLowerCase()} this vendor?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmBtnColor,
                    cancelButtonColor: '#6c757d',
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
                            checkbox.prop('checked', !isChecked); // Revert checkbox
                            Toast.fire({ icon: 'error', title: 'Failed/Unauthorized' });
                        });
                    } else {
                        checkbox.prop('checked', !isChecked); // Revert checkbox on cancel
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>