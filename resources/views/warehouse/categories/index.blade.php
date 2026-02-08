<x-app-layout title="Categories">
    <div class="container-fluid">
        {{-- HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.categories.partials.breadcrumb', ['title' => 'Categories'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-shape text-primary"></i> Categories
                        </h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- SEARCH & FILTER --}}
                            <form method="GET" class="d-flex flex-grow-1" style="max-width: 500px;">
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-end-0" placeholder="Search categories...">
                                    <select name="status" class="form-select border-start-0 border-end-0">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                    @if(request('search') || request('status') !== null)
                                        <a href="{{ route('warehouse.categories.index') }}" class="btn btn-outline-secondary"><i class="mdi mdi-close"></i></a>
                                    @endif
                                </div>
                            </form>

                            {{-- ACTIONS (Protected) --}}
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                <a href="{{ route('warehouse.categories.export') }}" class="btn btn-outline-primary"><i class="mdi mdi-download"></i> Export</a>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importCategoryModal">
                                    <i class="mdi mdi-upload"></i> Import
                                </button>
                                <a href="{{ route('warehouse.categories.create') }}" class="btn btn-success"><i class="mdi mdi-plus-circle"></i> Add Category</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">#</th>
                                <th class="py-3 text-muted fw-semibold small">ICON</th>
                                <th class="py-3 text-muted fw-semibold small">NAME</th>
                                <th class="py-3 text-muted fw-semibold small">CODE</th>
                                <th class="py-3 text-muted fw-semibold small text-center">SUBCATEGORIES</th>
                                <th class="py-3 text-muted fw-semibold small text-center">STATUS</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">{{ $loop->iteration + ($categories->currentPage()-1)*$categories->perPage() }}</td>
                                    <td class="py-3 fw-semibold">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $category->icon ?  Storage::url($category->icon) : 'https://placehold.co/40?text=IMG' }}" 
                                                 class="rounded bg-light border object-fit-cover" 
                                                 width="40" height="40">
                                            <span>{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 fw-semibold">{{ $category->name }}</td>
                                    <td class="py-3"><code class="bg-light px-2 py-1 rounded">{{ $category->code }}</code></td>
                                    <td class="py-3 text-center"><span class="badge bg-info bg-opacity-10 text-info">{{ $category->subcategories_count }}</span></td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" type="checkbox" 
                                                data-id="{{ $category->id }}" 
                                                {{ $category->is_active ? 'checked' : '' }}
                                                {{-- Disable if no permission --}}
                                                {{ (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories')) ? '' : 'disabled' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        {{-- View is always visible --}}
                                        <button class="btn btn-sm btn-outline-info me-1 view-btn" data-data="{{ json_encode($category) }}" title="View"><i class="mdi mdi-eye"></i></button>
                                        
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                            <a href="{{ route('warehouse.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                            <form method="POST" action="{{ route('warehouse.categories.destroy', $category) }}" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger delete-form" title="Delete"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5 text-muted">No categories found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($categories->hasPages())
                <div class="card-footer bg-white border-top py-3">{{ $categories->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>

    {{-- VIEW MODAL --}}
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-shape text-primary me-2"></i> Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Name</label>
                        <div class="fs-5 fw-semibold" id="viewName"></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Code</label>
                        <div class="text-monospace" id="viewCode"></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Status</label>
                        <div id="viewStatus"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('warehouse.categories._import-modal')
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // VIEW MODAL
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.dataset.data);
                    document.getElementById('viewName').textContent = data.name;
                    document.getElementById('viewCode').textContent = data.code;
                    const statusDiv = document.getElementById('viewStatus');
                    statusDiv.innerHTML = data.is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-secondary">Inactive</span>';
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                });
            });

            // STATUS TOGGLE (Only attach event if enabled)
            document.querySelectorAll('.status-toggle:not(:disabled)').forEach(checkbox => {
                checkbox.addEventListener('change', function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const status = this.checked ? 1 : 0;
                    const original = !this.checked;
                    
                    Swal.fire({
                        title: 'Are you sure?', text: 'Change status?', icon: 'warning',
                        showCancelButton: true, confirmButtonText: 'Yes'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("{{ route('warehouse.categories.status') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id, status })
                            }).then(res => res.json()).then(data => {
                                Swal.fire({ icon: 'success', title: 'Status updated', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                                checkbox.checked = status == 1;
                            }).catch(() => {
                                checkbox.checked = original;
                                Swal.fire('Error', 'Update failed', 'error');
                            });
                        } else { checkbox.checked = original; }
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>