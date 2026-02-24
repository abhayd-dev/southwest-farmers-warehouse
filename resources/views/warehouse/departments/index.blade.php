<x-app-layout title="Departments Management">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Departments',
            'items' => []
        ])

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">Department List</h5>
                            
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                            <a href="{{ route('warehouse.departments.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus me-1"></i>Add New
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Products</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departments as $dept)
                                    <tr>
                                        <td><span class="badge bg-light text-dark border">{{ $dept->code }}</span></td>
                                        <td class="fw-semibold">{{ $dept->name }}</td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $dept->products_count ?? 0 }} Items
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                    data-id="{{ $dept->id }}" {{ $dept->is_active ? 'checked' : '' }}
                                                    {{ (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories')) ? '' : 'disabled' }}>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                                <x-action-buttons 
                                                    :editUrl="route('warehouse.departments.edit', $dept->id)"
                                                    :deleteUrl="route('warehouse.departments.destroy', $dept->id)"
                                                />
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $departments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Scripts block remains same, logic for status toggle is inside JS which will respect disabled attribute --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.status-toggle:not(:disabled)'); // Only active for enabled
            
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const deptId = this.getAttribute('data-id');
                    const isChecked = this.checked;
                    const newStatus = isChecked ? 1 : 0;
                    
                    // 1. Revert visually immediately (wait for confirmation)
                    this.checked = !isChecked; 

                    Swal.fire({
                        title: isChecked ? 'Activate Department?' : 'Deactivate Department?',
                        text: isChecked ? "Department will be visible in products." : "Department will be hidden from selection!",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // 2. If confirmed, set the toggle to the desired state
                            this.checked = isChecked;

                            // 3. Send AJAX Request
                            fetch("{{ route('warehouse.departments.status') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    id: deptId,
                                    status: newStatus
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                Swal.fire({
                                    title: 'Updated!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                // Revert switch if API fails
                                this.checked = !isChecked;
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            });
                        }
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>