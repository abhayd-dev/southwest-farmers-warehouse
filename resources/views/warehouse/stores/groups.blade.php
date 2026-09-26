<x-app-layout title="Store Groups">
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 bg-white p-3 rounded shadow-sm gap-3">
            <div>
                <nav class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="page-breadcrumb-link">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stores.index') }}" class="page-breadcrumb-link">All Stores</a></li>
                        <li class="breadcrumb-item text-muted">Store Groups</li>
                    </ol>
                </nav>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="mdi mdi-folder-multiple me-2"></i> Store Groups
                </h4>
                <p class="text-muted mb-0 small mt-1">Group stores together (e.g. by region). On the Store side, a group can be assigned to a staff member such as a Regional Manager, who can then switch between every store in it.</p>
            </div>
            @can('edit_stores')
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#groupModal-new">
                    <i class="mdi mdi-plus me-1"></i> New Group
                </button>
            @endcan
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        {{-- GROUPS --}}
        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4 py-3">Group</th>
                                <th class="py-3">Stores</th>
                                <th class="py-3 text-center">Staff Assigned</th>
                                <th class="text-end pe-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $group)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $group->name }}</h6>
                                        @if ($group->description)
                                            <small class="text-muted">{{ $group->description }}</small>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @forelse ($group->stores as $store)
                                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $store->store_name }}</span>
                                        @empty
                                            <span class="text-muted small">No stores yet</span>
                                        @endforelse
                                    </td>
                                    <td class="py-3 text-center">{{ $group->staff_count }}</td>
                                    <td class="text-end pe-4 py-3">
                                        @can('edit_stores')
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#groupModal-{{ $group->id }}">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </button>
                                            <form action="{{ route('warehouse.stores.groups.destroy', $group) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Delete this group? Its stores and staff stay, they are just no longer grouped.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No store groups yet. Create one to group stores by region.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CREATE / EDIT MODALS --}}
        @can('edit_stores')
            @foreach ($groups->concat([null]) as $group)
                @php $key = $group?->id ?? 'new'; @endphp
                <div class="modal fade" id="groupModal-{{ $key }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-scrollable">
                        <form class="modal-content" method="POST"
                              action="{{ $group ? route('warehouse.stores.groups.update', $group) : route('warehouse.stores.groups.store') }}">
                            @csrf
                            @if ($group) @method('PUT') @endif
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $group ? 'Edit Store Group' : 'New Store Group' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Group Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required maxlength="255"
                                           value="{{ $group->name ?? '' }}" placeholder="e.g. Houston Region">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <input type="text" name="description" class="form-control" maxlength="255" value="{{ $group->description ?? '' }}">
                                </div>
                                <label class="form-label fw-bold">Stores in this group</label>
                                <div class="border rounded p-2" style="max-height: 260px; overflow-y: auto;">
                                    @foreach ($stores as $store)
                                        @php $inOther = $store->store_group_id && $store->store_group_id !== $group?->id; @endphp
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="store_ids[]" value="{{ $store->id }}"
                                                   id="g{{ $key }}-s{{ $store->id }}"
                                                   {{ $group && $store->store_group_id === $group->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="g{{ $key }}-s{{ $store->id }}">
                                                {{ $store->store_name }} <small class="text-muted">{{ $store->city }}</small>
                                                @if ($inOther)
                                                    <small class="text-warning">(in {{ $groups->firstWhere('id', $store->store_group_id)?->name }} — ticking moves it here)</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Group</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endcan
    </div>
</x-app-layout>
