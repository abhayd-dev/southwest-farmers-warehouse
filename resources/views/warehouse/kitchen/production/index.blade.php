@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Production Logs</h4>
                <div class="page-title-right">
                    <a href="{{ route('kitchen.production.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Log New Production
                    </a>
                    <a href="{{ route('kitchen.production.leftovers') }}" class="btn btn-warning ms-2">
                        <i class="mdi mdi-food-off me-1"></i> Leftover Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Menu Item</th>
                                <th>Kitchen</th>
                                <th>Qty Made</th>
                                <th>Yield (Plates)</th>
                                <th>Daily Target</th>
                                <th>Produced At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productions as $log)
                            <tr>
                                <td>{{ $log->menuItem->name ?? 'N/A' }}</td>
                                <td>{{ $log->kitchenLocation->name ?? 'N/A' }}</td>
                                <td>{{ $log->quantity_made }} {{ $log->quantity_unit }}</td>
                                <td>{{ $log->yield_plates ?? '-' }}</td>
                                <td>{{ $log->daily_target ?: '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->produced_at)->format('d M Y, h:i A') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">No production logs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
