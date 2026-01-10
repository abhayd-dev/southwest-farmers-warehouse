<x-app-layout title="Warehouse | Inventory System">

    <div class="container-fluid">

        <!-- Header -->
        <div class="py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h4 class="fs-18 fw-semibold m-0">Warehouse (Main)</h4>

            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search warehouse...">

                    <select name="status" class="form-select" style="max-width:130px;">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button class="btn btn-secondary">
                        <i class="mdi mdi-magnify"></i>
                    </button>

                    @if (request('search') || request('status') !== null)
                        <a href="{{ route('warehouse.index') }}" class="btn btn-danger">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses as $warehouse)
                                <tr>
                                    <td>{{ $warehouse->warehouse_name }}</td>
                                    <td>{{ $warehouse->code }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" type="checkbox"
                                                data-id="{{ $warehouse->id }}"
                                                {{ $warehouse->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('warehouse.edit', $warehouse) }}"
                                            class="btn btn-sm btn-light border">
                                            <i class="mdi mdi-pencil text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No warehouse found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $warehouses->withQueryString()->links() }}
                </div>

            </div>
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
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        checkbox.prop('checked', !checkbox.prop('checked'));

                        $.post("{{ route('warehouse.update-status') }}", {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        }, function(res) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false,
                                title: res.message
                            });
                        });
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>
