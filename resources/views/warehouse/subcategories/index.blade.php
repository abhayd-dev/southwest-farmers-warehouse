<x-app-layout title="Subcategories">
    <div class="container-fluid">
        {{-- HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.subcategories.partials.breadcrumb', [
                            'title' => 'Subcategories',
                        ])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-tag text-primary"></i> Subcategories
                        </h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- SEARCH & FILTER --}}
                            <form method="GET" class="d-flex flex-grow-1" style="max-width: 500px;">
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control border-end-0" placeholder="Search subcategories...">
                                    <select name="status" class="form-select border-start-0 border-end-0">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive
                                        </option>
                                    </select>
                                    <button class="btn btn-primary" type="submit"><i
                                            class="mdi mdi-magnify"></i></button>
                                    @if (request('search') || request('status') !== null)
                                        <a href="{{ route('warehouse.subcategories.index') }}"
                                            class="btn btn-outline-secondary"><i class="mdi mdi-close"></i></a>
                                    @endif
                                </div>
                            </form>

                            {{-- ACTIONS (Protected) --}}
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                {{-- Using manage_categories for broader access --}}
                                <a href="{{ route('warehouse.subcategories.export') }}"
                                    class="btn btn-outline-primary"><i class="mdi mdi-download"></i> Export</a>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#importSubCategoryModal">
                                    <i class="mdi mdi-upload"></i> Import
                                </button>
                                <a href="{{ route('warehouse.subcategories.create') }}" class="btn btn-success"><i
                                        class="mdi mdi-plus-circle"></i> Add Subcategory</a>
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
                                    <td class="py-3"><span
                                            class="badge bg-primary bg-opacity-10 text-primary">{{ $sub->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 fw-semibold">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $sub->icon ? Storage::url($sub->icon) : 'https://placehold.co/40?text=IMG' }}"
                                                class="rounded bg-light border object-fit-cover" width="40"
                                                height="40">
                                            <span>{{ $sub->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3"><code
                                            class="bg-light px-2 py-1 rounded">{{ $sub->code }}</code></td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" type="checkbox"
                                                data-id="{{ $sub->id }}" {{ $sub->is_active ? 'checked' : '' }}
                                                {{ auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories') ? '' : 'disabled' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button class="btn btn-sm btn-outline-info me-1 view-btn"
                                            data-data="{{ json_encode($sub) }}"
                                            data-cat="{{ $sub->category->name ?? 'N/A' }}" title="View"><i
                                                class="mdi mdi-eye"></i></button>

                                        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                            <a href="{{ route('warehouse.subcategories.edit', $sub) }}"
                                                class="btn btn-sm btn-outline-primary me-1" title="Edit"><i
                                                    class="mdi mdi-pencil"></i></a>
                                            <form method="POST"
                                                action="{{ route('warehouse.subcategories.destroy', $sub) }}"
                                                class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger delete-form"
                                                    title="Delete"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif
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
                <div class="card-footer bg-white border-top py-3">{{ $subcategories->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- View Modal & Scripts remain same --}}
    @include('warehouse.subcategories._import-modal')
    {{-- (Scripts block as in original, just protected Toggle Listener) --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // VIEW MODAL (Same as before)
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
