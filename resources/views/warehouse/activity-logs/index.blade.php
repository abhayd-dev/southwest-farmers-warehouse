<x-app-layout title="Activity Audit Logs">
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="mdi mdi-shield-search text-primary me-2"></i> Audit Logs</h4>
                <small class="text-muted">Track all system activities and changes</small>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="action" class="form-select">
                            <option value="">All Actions</option>
                            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-filter me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">User / Actor</th>
                            <th>Action</th>
                            <th>Module / Subject</th>
                            <th>Changes (Old vs New)</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $log->causer->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $log->ip_address ?? 'IP N/A' }}</small>
                            </td>
                            <td>
                                @php
                                    $badges = [
                                        'created' => 'success',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        'login'   => 'info'
                                    ];
                                    $color = $badges[$log->action] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} text-uppercase">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">
                                    {{ class_basename($log->subject_type) }}
                                </span>
                                <small class="text-muted d-block">ID: {{ $log->subject_id }}</small>
                            </td>
                            <td>
                                @if($log->action == 'updated' && isset($log->properties['old']))
                                    <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#logDetails{{ $log->id }}">
                                        View Changes <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <div class="collapse mt-2" id="logDetails{{ $log->id }}">
                                        <div class="card card-body bg-light p-2 small font-monospace">
                                            @foreach($log->properties['new'] as $key => $val)
                                                <div class="mb-1">
                                                    <span class="text-muted">{{ ucfirst($key) }}:</span> 
                                                    <span class="text-danger text-decoration-line-through me-1">{{ $log->properties['old'][$key] ?? 'null' }}</span>
                                                    <i class="mdi mdi-arrow-right small text-muted"></i>
                                                    <span class="text-success fw-bold">{{ $val }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($log->description)
                                    <span class="text-muted">{{ $log->description }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $log->created_at->format('d M Y') }} <br>
                                {{ $log->created_at->format('h:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>