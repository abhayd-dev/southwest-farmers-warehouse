<x-app-layout title="Subcategories">
    <div class="container-fluid">
        {{-- HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.subcategories.partials.breadcrumb', ['title' => 'Subcategories'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-tag text-primary"></i> Subcategories
                        </h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                            {{-- SEARCH & FILTER --}}
                            <form method="GET" class="d-flex flex-grow-1" style="max-width: 500px;">
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control border-end-0" placeholder="Search subcategories...">
                                    <select name="status" class="form-select border-start-0 border-end-0" style="max-width: 120px;">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    
                                    {{-- Apply Button --}}
                                    <button class="btn btn-primary" type="submit" title="Apply Filter">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>

                                    {{-- Reset Button --}}
                                    <a href="{{ route('warehouse.subcategories.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                        <i class="mdi mdi-refresh"></i>
                                    </a>
                                </div>
                            </form>

                            {{-- ACTIONS (Protected) --}}
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('warehouse.subcategories.export') }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                                        <i class="mdi mdi-download"></i> <span class="d-none d-md-inline">Export</span>
                                    </a>
                                    <button type="button" class="btn btn-outline-success d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importSubCategoryModal">
                                        <i class="mdi mdi-upload"></i> <span class="d-none d-md-inline">Import</span>
                                    </button>
                                    <a href="{{ route('warehouse.subcategories.create') }}" class="btn btn-success d-flex align-items-center gap-1 text-nowrap">
                                        <i class="mdi mdi-plus-circle"></i> Add Subcategory
                                    </a>
                                </div>
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
                                <th class="py-3 text-muted fw-semibold small">PARENT CATEGORY</th>
                                <th class="py-3 text-muted fw-semibold small">ICON & NAME</th>
                                <th class="py-3 text-muted fw-semibold small">CODE</th>
                                <th class="py-3 text-muted fw-semibold small text-center">STATUS</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subcategories as $sub)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">
                                        {{ $loop->iteration + ($subcategories->currentPage() - 1) * $subcategories->perPage() }}
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $sub->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 fw-semibold">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $sub->icon ? Storage::url($sub->icon) : asset('assets/images/placeholder.svg') }}"
                                                class="rounded bg-light border object-fit-cover" width="40" height="40">
                                            <span>{{ $sub->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3"><code class="bg-light px-2 py-1 rounded">{{ $sub->code }}</code></td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" type="checkbox"
                                                data-id="{{ $sub->id }}" {{ $sub->is_active ? 'checked' : '' }}
                                                {{ auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories') ? '' : 'disabled' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <x-action-buttons 
                                            :viewUrl="null" 
                                            :data="$sub"
                                            :editUrl="auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories') ? route('warehouse.subcategories.edit', $sub) : null"
                                            :deleteUrl="auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories') ? route('warehouse.subcategories.destroy', $sub) : null"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No subcategories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($subcategories->hasPages())
                <div class="card-footer bg-white border-top py-3">{{ $subcategories->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>

    {{-- VIEW MODAL --}}
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-tag text-primary me-2"></i> Subcategory Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Name</label>
                        <div class="fs-5 fw-semibold" id="viewName"></div>
                    </div>
                     <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Parent Category</label>
                        <div class="fs-6 text-primary" id="viewCategory"></div>
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

    @include('warehouse.subcategories._import-modal')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // VIEW MODAL
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const data = JSON.parse(this.dataset.data);
                        const catName = this.dataset.cat;
                        document.getElementById('viewName').textContent = data.name;
                        document.getElementById('viewCode').textContent = data.code;
                        document.getElementById('viewCategory').textContent = catName;
                        document.getElementById('viewStatus').innerHTML = data.is_active ?
                            '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-secondary">Inactive</span>';
                        new bootstrap.Modal(document.getElementById('viewModal')).show();
                    });
                });

                // STATUS TOGGLE
                document.querySelectorAll('.status-toggle:not(:disabled)').forEach(checkbox => {
                    checkbox.addEventListener('change', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const status = this.checked ? 1 : 0;
                        const original = !this.checked;
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'Change status?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch("{{ route('warehouse.subcategories.status') }}", {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        id,
                                        status
                                    })
                                }).then(res => res.json()).then(data => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Status updated',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                    checkbox.checked = status == 1;
                                }).catch(() => {
                                    checkbox.checked = original;
                                    Swal.fire('Error', 'Update failed', 'error');
                                });
                            } else {
                                checkbox.checked = original;
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>