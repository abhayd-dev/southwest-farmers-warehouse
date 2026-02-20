<x-app-layout title="Market Management">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Markets',
            'items' => []
        ])

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">Market List</h5>
                            
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#addMarketModal">
                                <i class="mdi mdi-plus me-1"></i>Add New Market
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($markets as $market)
                                    <tr>
                                        <td>{{ $market->id }}</td>
                                        <td class="fw-semibold">{{ $market->name }}</td>
                                        <td>{{ $market->description }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                    data-id="{{ $market->id }}" {{ $market->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-market-btn"
                                                data-id="{{ $market->id }}"
                                                data-name="{{ $market->name }}"
                                                data-description="{{ $market->description }}">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $markets->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Market Modal -->
    <div class="modal fade" id="addMarketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('warehouse.markets.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Market</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Market Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Houston">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Market</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Market Modal -->
    <div class="modal fade" id="editMarketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Market</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Market Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Market</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Modal logic
            document.querySelectorAll('.edit-market-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');
                    let name = this.getAttribute('data-name');
                    let description = this.getAttribute('data-description');
                    
                    document.getElementById('editName').value = name;
                    document.getElementById('editDescription').value = description;
                    document.getElementById('editForm').action = "/warehouse/markets/" + id;
                    
                    new bootstrap.Modal(document.getElementById('editMarketModal')).show();
                });
            });

            // Status toggle logic
            document.querySelectorAll('.status-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const marketId = this.getAttribute('data-id');
                    const newStatus = this.checked ? 1 : 0;
                    
                    fetch("{{ route('warehouse.markets.status') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            id: marketId,
                            status: newStatus
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        // success silently or message
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
