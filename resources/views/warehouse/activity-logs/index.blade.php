<x-app-layout title="Activity Audit Logs">
    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3 px-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-1">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-shield-search text-primary me-2"></i> Audit Logs
                        </h4>
                        <small class="text-muted">Track all system activities, data changes, and security events</small>
                    </div>

                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                        <button class="btn btn-outline-secondary shadow-sm flex-fill flex-md-grow-0" onclick="window.location.reload()">
                            <i class="mdi mdi-refresh me-1"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTERS CARD --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-2">
                    <div class="col-12 col-md-3">
                        <select name="user_id" class="form-select border-light shadow-sm">
                            <option value="">All Users / Actors</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="action" class="form-select border-light shadow-sm">
                            <option value="">All Action Types</option>
                            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <input type="date" name="date" class="form-control border-light shadow-sm" value="{{ request('date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                <i class="mdi mdi-filter me-1"></i> Apply Filter
                            </button>
                            @if(request()->anyFilled(['user_id', 'action', 'date']))
                                <a href="{{ route('warehouse.activity-logs.index') }}" class="btn btn-outline-danger shadow-sm">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- LOGS TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase small fw-bold">User / Actor</th>
                                <th class="py-3 text-uppercase small fw-bold">Action</th>
                                <th class="py-3 text-uppercase small fw-bold">Module / Subject</th>
                                <th class="py-3 text-uppercase small fw-bold">Data Changes</th>
                                <th class="py-3 pe-4 text-uppercase small fw-bold text-end">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px;">
                                            <i class="mdi mdi-account text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $log->causer->name ?? 'System' }}</div>
                                            <small class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $log->ip_address ?? '0.0.0.0' }}</small>
                                        </div>
                                    </div>
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
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 px-2 py-1">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">
                                        {{ class_basename($log->subject_type) }}
                                    </span>
                                    <small class="text-muted d-block">ID: #{{ $log->subject_id }}</small>
                                </td>
                                <td>
                                    @if($log->action == 'updated' && isset($log->properties['old']))
                                        <button class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#logDetails{{ $log->id }}">
                                            View Diff <i class="mdi mdi-chevron-down ms-1"></i>
                                        </button>
                                        <div class="collapse mt-2" id="logDetails{{ $log->id }}">
                                            <div class="p-2 border rounded bg-light" style="min-width: 250px;">
                                                @foreach($log->properties['new'] as $key => $val)
                                                    @if(isset($log->properties['old'][$key]) && $log->properties['old'][$key] != $val)
                                                        <div class="mb-1 small">
                                                            <strong class="text-muted text-uppercase" style="font-size: 0.65rem;">{{ $key }}:</strong><br>
                                                            <span class="text-danger text-decoration-line-through me-1">{{ $log->properties['old'][$key] }}</span>
                                                            <i class="mdi mdi-arrow-right text-muted"></i>
                                                            <span class="text-success fw-bold">{{ $val }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($log->description)
                                        <span class="text-muted small text-wrap d-block" style="max-width: 250px;">{{ $log->description }}</span>
                                    @else
                                        <span class="text-muted italic small">No metadata recorded</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="fw-bold text-dark small">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $log->created_at->format('h:i:s A') }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="opacity-25 mb-2">
                                        <i class="mdi mdi-database-off" style="font-size: 3rem;"></i>
                                    </div>
                                    <h6 class="text-muted">No activity logs found matching the criteria.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>