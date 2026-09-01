@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 text-primary"><i class="mdi mdi-calendar-clock me-2"></i>Daily Availability & Catering Matrix</h4>
                    <p class="text-muted mb-0">Manage daily menu scheduling, live stock toggles, advance notice periods, and rush fee percentages.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('kitchen.menu-items.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="mdi mdi-food me-1"></i> Manage Menu Items
                    </a>
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

    <!-- Quick Stats -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card card-animate border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3 p-2">
                                <i class="mdi mdi-silverware-fork-knife"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Total Menu Items</p>
                            <h4 class="mb-0">{{ $menuItems->total() }}</h4>
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
                                <i class="mdi mdi-check-decagram"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Available Today</p>
                            <h4 class="mb-0 text-success">{{ \App\Models\MenuItem::where('is_available_today', true)->count() }}</h4>
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
                                <i class="mdi mdi-party-popper"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Catering Exclusive</p>
                            <h4 class="mb-0 text-warning">{{ \App\Models\MenuItem::where('is_catering_only', true)->count() }}</h4>
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
                                <i class="mdi mdi-pot-steam"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Pre-Cooked Items</p>
                            <h4 class="mb-0 text-info">{{ \App\Models\MenuItem::where('is_pre_cooked', true)->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('kitchen.availability.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search menu item by name..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">-- All Categories --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="catering_filter" class="form-select form-select-sm">
                                <option value="">-- All Item Types --</option>
                                <option value="regular" {{ request('catering_filter') == 'regular' ? 'selected' : '' }}>Regular Dine-In / Takeaway</option>
                                <option value="catering_only" {{ request('catering_filter') == 'catering_only' ? 'selected' : '' }}>Catering Exclusive Only</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="mdi mdi-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('kitchen.availability.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Matrix Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="min-width: 200px;">Menu Item</th>
                                    <th>Category</th>
                                    <th class="text-center" style="width: 140px;">Live Status Today</th>
                                    <th class="text-center" style="min-width: 240px;">Weekly Schedule Matrix</th>
                                    <th class="text-center" style="min-width: 130px;">Catering Only</th>
                                    <th class="text-center" style="min-width: 120px;">Notice Required</th>
                                    <th class="text-center" style="min-width: 110px;">Rush Fee</th>
                                    <th class="text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menuItems as $item)
                                @php
                                    $itemDays = is_array($item->available_days) ? $item->available_days : ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $item->name }}</h6>
                                                <small class="text-muted">${{ number_format($item->price, 2) }}</small>
                                                @if($item->is_pre_cooked)
                                                    <span class="badge bg-info-subtle text-info fs-11 ms-1">Pre-Cooked</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $item->menuCategory->name ?? 'General' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('kitchen.availability.toggle-today', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($item->is_available_today)
                                                <button type="submit" class="btn btn-sm btn-success w-100 py-1" title="Click to mark unavailable today">
                                                    <i class="mdi mdi-check-circle me-1"></i> Available
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-danger w-100 py-1" title="Click to mark available today">
                                                    <i class="mdi mdi-close-circle me-1"></i> Sold Out
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @foreach($daysOfWeek as $day)
                                                @php $activeDay = in_array($day, $itemDays); @endphp
                                                <span class="badge {{ $activeDay ? 'bg-primary' : 'bg-light text-muted border' }}" style="min-width: 32px; font-size: 10px;">
                                                    {{ $day }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($item->is_catering_only)
                                            <span class="badge bg-warning text-dark"><i class="mdi mdi-party-popper me-1"></i> Catering Only</span>
                                        @else
                                            <span class="badge bg-light text-muted border">Standard & Catering</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary fs-12 px-2 py-1">
                                            <i class="mdi mdi-clock-outline me-1"></i>{{ $item->advance_notice_days ?? 7 }} Days
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->rush_fee_percentage > 0)
                                            <span class="badge bg-danger-subtle text-danger fs-12 px-2 py-1">
                                                +{{ number_format($item->rush_fee_percentage, 0) }}%
                                            </span>
                                        @else
                                            <span class="text-muted fs-12">0%</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                            <i class="mdi mdi-pencil"></i> Configure
                                        </button>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('kitchen.availability.update', $item) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header bg-light">
                                                            <h5 class="modal-title" id="modalLabel{{ $item->id }}">
                                                                <i class="mdi mdi-tune me-1"></i> Configure: {{ $item->name }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Live Toggle -->
                                                            <div class="form-check form-switch mb-3 p-3 bg-light rounded">
                                                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="todayCheck{{ $item->id }}" name="is_available_today" value="1" {{ $item->is_available_today ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-semibold" for="todayCheck{{ $item->id }}">
                                                                    Available for Order Today
                                                                </label>
                                                                <div class="form-text mt-0">Toggle off to immediately mark as out-of-stock on POS and Online.</div>
                                                            </div>

                                                            <!-- Days of Week Matrix -->
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Standard Weekly Availability</label>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    @foreach($daysOfWeek as $day)
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="available_days[]" value="{{ $day }}" id="day_{{ $item->id }}_{{ $day }}" {{ in_array($day, $itemDays) ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="day_{{ $item->id }}_{{ $day }}">{{ $day }}</label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>

                                                            <hr>
                                                            <h6 class="text-primary mb-3"><i class="mdi mdi-party-popper me-1"></i> Catering Rules & Restrictions</h6>

                                                            <!-- Catering Only Toggle -->
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="cateringOnly{{ $item->id }}" name="is_catering_only" value="1" {{ $item->is_catering_only ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-semibold" for="cateringOnly{{ $item->id }}">
                                                                    Catering Exclusive Order Only
                                                                </label>
                                                                <div class="form-text">If checked, item is hidden from daily retail POS and only available for large pre-orders.</div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label fw-semibold">Advance Notice (Days)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" name="advance_notice_days" class="form-control" min="0" max="90" value="{{ $item->advance_notice_days ?? 7 }}">
                                                                        <span class="input-group-text">Days</span>
                                                                    </div>
                                                                    <div class="form-text">Minimum lead time for catering booking.</div>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label fw-semibold">Rush Fee Percentage</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" name="rush_fee_percentage" class="form-control" min="0" max="100" step="0.5" value="{{ $item->rush_fee_percentage ?? 0 }}">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                    <div class="form-text">Fee applied if ordered inside notice window.</div>
                                                                </div>
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
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="mdi mdi-information-outline fs-3 d-block mb-1"></i>
                                        No menu items match your filter criteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($menuItems->hasPages())
                <div class="card-footer py-2">
                    <div class="d-flex justify-content-end">
                        {{ $menuItems->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
