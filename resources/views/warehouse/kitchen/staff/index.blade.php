@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 text-primary"><i class="mdi mdi-account-clock me-2"></i>Kitchen Staff Scheduling & Timesheets</h4>
                    <p class="text-muted mb-0">Manage shift assignments, kitchen stations, live clock-in/out logs, and daily attendance.</p>
                </div>
                <div class="page-title-right d-flex gap-2">
                    <form method="GET" action="{{ route('kitchen.staff.index') }}" class="d-flex align-items-center gap-2">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}" onchange="this.form.submit()">
                    </form>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleShiftModal">
                        <i class="mdi mdi-plus-circle me-1"></i> Schedule Shift
                    </button>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#clockInModal">
                        <i class="mdi mdi-clock-in me-1"></i> Clock In Staff
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card card-animate border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3 p-2">
                                <i class="mdi mdi-calendar-check"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Scheduled Shifts</p>
                            <h4 class="mb-0">{{ $stats['total_scheduled_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-animate border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3 p-2">
                                <i class="mdi mdi-account-check"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Currently Clocked In</p>
                            <h4 class="mb-0 text-success">{{ $stats['currently_clocked_in'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-animate border-start border-info border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3 p-2">
                                <i class="mdi mdi-clock-check-outline"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Completed Shifts</p>
                            <h4 class="mb-0 text-info">{{ $stats['completed_shifts_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-animate border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3 p-2">
                                <i class="mdi mdi-timer-sand"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Hours Worked</p>
                            <h4 class="mb-0 text-warning">{{ number_format($stats['total_hours_today'], 1) }} hrs</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light p-2">
                    <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#shiftsTab" role="tab">
                                <i class="mdi mdi-calendar-month me-1"></i> Shift Roster ({{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#timesheetsTab" role="tab">
                                <i class="mdi mdi-clock-outline me-1"></i> Live Time Clock & Timesheets
                                @if($activeClockIns->count() > 0)
                                    <span class="badge bg-success ms-1">{{ $activeClockIns->count() }} Online</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- TAB 1: SHIFT ROSTER -->
                        <div class="tab-pane active" id="shiftsTab" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Staff Member</th>
                                            <th>Kitchen Location</th>
                                            <th>Assigned Station</th>
                                            <th>Shift Timing</th>
                                            <th class="text-center">Status</th>
                                            <th>Notes</th>
                                            <th class="text-center" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($shifts as $shift)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $shift->staff->name ?? 'Staff #' . $shift->ware_user_id }}</div>
                                                <small class="text-muted">{{ $shift->staff->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $shift->kitchenLocation->name ?? 'Main Kitchen' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">{{ $shift->station }}</span>
                                            </td>
                                            <td>
                                                <i class="mdi mdi-clock-time-four-outline text-muted me-1"></i>
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                            </td>
                                            <td class="text-center">
                                                @if($shift->status === 'scheduled')
                                                    <span class="badge bg-warning text-dark">Scheduled</span>
                                                @elseif($shift->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($shift->status === 'absent')
                                                    <span class="badge bg-danger">Absent</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($shift->status) }}</span>
                                                @endif
                                            </td>
                                            <td><small class="text-muted">{{ $shift->notes ?: '-' }}</small></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editShiftModal{{ $shift->id }}">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('kitchen.staff.shift.destroy', $shift) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this shift?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="mdi mdi-trash-can-outline"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Edit Shift Modal -->
                                                <div class="modal fade text-start" id="editShiftModal{{ $shift->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="{{ route('kitchen.staff.shift.update', $shift) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header bg-light">
                                                                    <h5 class="modal-title"><i class="mdi mdi-pencil me-1"></i> Edit Shift: {{ $shift->staff->name ?? 'Staff' }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold">Status</label>
                                                                        <select name="status" class="form-select">
                                                                            <option value="scheduled" {{ $shift->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                                            <option value="completed" {{ $shift->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                                            <option value="absent" {{ $shift->status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                                            <option value="cancelled" {{ $shift->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold">Station</label>
                                                                        <input type="text" name="station" class="form-control" value="{{ $shift->station }}">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold">Notes</label>
                                                                        <textarea name="notes" class="form-control" rows="2">{{ $shift->notes }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-calendar-remove fs-3 d-block mb-1"></i>
                                                No shifts scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: LIVE TIMESHEETS -->
                        <div class="tab-pane" id="timesheetsTab" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Staff Member</th>
                                            <th>Location</th>
                                            <th>Clock In Time</th>
                                            <th>Clock Out Time</th>
                                            <th>Break (Mins)</th>
                                            <th>Total Hours</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width: 140px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($timeLogs as $log)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $log->staff->name ?? 'Staff #' . $log->ware_user_id }}</div>
                                                <small class="text-muted">{{ $log->staff->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $log->kitchenLocation->name ?? 'Main Kitchen' }}</span>
                                            </td>
                                            <td>
                                                <i class="mdi mdi-clock-in text-success me-1"></i>
                                                {{ \Carbon\Carbon::parse($log->clock_in_at)->displayTime()->format('h:i:s A') }}
                                            </td>
                                            <td>
                                                @if($log->clock_out_at)
                                                    <i class="mdi mdi-clock-out text-danger me-1"></i>
                                                    {{ \Carbon\Carbon::parse($log->clock_out_at)->displayTime()->format('h:i:s A') }}
                                                @else
                                                    <span class="badge bg-success-subtle text-success">In Progress</span>
                                                @endif
                                            </td>
                                            <td>{{ $log->break_minutes }}m</td>
                                            <td>
                                                @if($log->total_hours)
                                                    <strong>{{ number_format($log->total_hours, 2) }} hrs</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($log->status === 'clocked_in')
                                                    <span class="badge bg-success"><i class="mdi mdi-circle-medium"></i> On Duty</span>
                                                @else
                                                    <span class="badge bg-secondary">Clocked Out</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!$log->clock_out_at)
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#clockOutModal{{ $log->id }}">
                                                        <i class="mdi mdi-clock-out me-1"></i> Clock Out
                                                    </button>

                                                    <!-- Clock Out Modal -->
                                                    <div class="modal fade text-start" id="clockOutModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form action="{{ route('kitchen.staff.clock-out', $log) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-header bg-danger text-white">
                                                                        <h5 class="modal-title text-white"><i class="mdi mdi-clock-out me-1"></i> Clock Out: {{ $log->staff->name ?? 'Staff' }}</h5>
                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p class="mb-3">
                                                                            Clock-in time recorded: <strong>{{ \Carbon\Carbon::parse($log->clock_in_at)->displayTime()->format('h:i A') }}</strong>
                                                                        </p>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Break Deducted (Minutes)</label>
                                                                            <input type="number" name="break_minutes" class="form-control" value="0" min="0" max="240">
                                                                            <div class="form-text">Minutes to subtract from calculated shift hours.</div>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-semibold">Shift Notes (Optional)</label>
                                                                            <textarea name="notes" class="form-control" rows="2" placeholder="Any handover notes..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Clock Out</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-12">Completed</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-account-clock-outline fs-3 d-block mb-1"></i>
                                                No time logs recorded for this date.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Schedule Shift -->
<div class="modal fade" id="scheduleShiftModal" tabindex="-1" aria-labelledby="scheduleShiftLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('kitchen.staff.shift.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="scheduleShiftLabel"><i class="mdi mdi-calendar-plus me-1"></i> Schedule New Shift</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Member *</label>
                        <select name="ware_user_id" class="form-select" required>
                            <option value="">-- Select Staff --</option>
                            @foreach($staffMembers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kitchen Location *</label>
                        <select name="kitchen_location_id" class="form-select" required>
                            @foreach($kitchenLocations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assigned Station / Role *</label>
                        <select name="station" class="form-select" required>
                            <option value="Main Kitchen">Main Kitchen (Stews & Rice)</option>
                            <option value="Bakery Station">Bakery Station (Puff Puff & Pastries)</option>
                            <option value="Soup Prep">Soup Prep (Egusi, Ogbono, etc.)</option>
                            <option value="Meat & Grilling">Meat & Grilling</option>
                            <option value="Packaging & Orders">Packaging & Orders</option>
                            <option value="Head Chef / Supervisor">Head Chef / Supervisor</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-semibold">Shift Date *</label>
                            <input type="date" name="shift_date" class="form-control" value="{{ $selectedDate }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" value="08:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Time *</label>
                            <input type="time" name="end_time" class="form-control" value="16:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Special prep instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Clock In -->
<div class="modal fade" id="clockInModal" tabindex="-1" aria-labelledby="clockInLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('kitchen.staff.clock-in') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="clockInLabel"><i class="mdi mdi-clock-in me-1"></i> Live Clock In</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Member *</label>
                        <select name="ware_user_id" class="form-select" required>
                            <option value="">-- Select Staff Member --</option>
                            @foreach($staffMembers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kitchen Location</label>
                        <select name="kitchen_location_id" class="form-select">
                            <option value="">-- Default Location --</option>
                            @foreach($kitchenLocations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Arrived on time for morning shift..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Clock In Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables & UI Polish -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables for Timesheets and Roster
        $('.table-hover').DataTable({
            pageLength: 25,
            ordering: false,
            language: { search: "", searchPlaceholder: "Search records..." }
        });
    });
</script>
@endpush
