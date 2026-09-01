@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Leftover Food Report</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>Date</th>
                                <th>Menu Item</th>
                                <th>Kitchen</th>
                                <th>Produced</th>
                                <th>Sold</th>
                                <th>Leftover</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->log_date)->format('d M Y') }}</td>
                                <td>{{ $log->menuItem->name ?? 'N/A' }}</td>
                                <td>{{ $log->kitchenLocation->name ?? 'N/A' }}</td>
                                <td>{{ $log->quantity_produced }}</td>
                                <td>{{ $log->quantity_sold }}</td>
                                <td>
                                    <span class="badge {{ $log->quantity_leftover > 0 ? 'bg-danger' : 'bg-success' }}">
                                        {{ $log->quantity_leftover }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">No leftover logs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
