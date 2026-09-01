@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sales Ranking Report</h4>
                <div class="page-title-right">
                    <form class="d-flex gap-2">
                        <select class="form-select form-select-sm">
                            <option>Today</option>
                            <option>This Week</option>
                            <option>This Month</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">This report shows the best-selling menu items based on actual point-of-sale data, helping you plan daily kitchen production targets.</p>
                    <table id="salesRankingTable" class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Menu Item</th>
                                <th>Category</th>
                                <th>Total Qty Sold</th>
                                <th>Estimated Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rank = 1; @endphp
                            @forelse($rankedItems as $item)
                            <tr>
                                <td>#{{ $rank++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->menuCategory->name ?? 'Uncategorized' }}</td>
                                <td><strong>{{ number_format($item->total_quantity_sold) }}</strong></td>
                                <td>${{ number_format($item->total_revenue, 2) }}</td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#salesRankingTable').DataTable({
            "order": [[ 0, "asc" ]],
            "pageLength": 25,
            "language": {
                "emptyTable": "No sales data available."
            }
        });
    });
</script>
@endsection

@section('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection
