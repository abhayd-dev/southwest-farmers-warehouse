<x-app-layout title="Recall Request #{{ $recall->id }}">

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Recall Request #{{ $recall->id }}</h4>
            <p class="text-muted">Store: {{ $recall->store->store_name }} | Product: {{ $recall->product->product_name }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">

            @if($recall->status == 'pending_store_approval')
                <div class="alert alert-warning">Waiting for store approval</div>
            @endif

            @if(in_array($recall->status, ['approved_by_store', 'partial_approved']))
                <div class="card mb-4">
                    <div class="card-header">Dispatch Stock (Store Side Action)</div>
                    <div class="card-body">
                        <form action="{{ route('warehouse.stock-control.recall.dispatch', $recall) }}" method="POST">
                            @csrf
                            <!-- Add batch selector here similar to stock request dispatch -->
                            <button type="submit" class="btn btn-success">Dispatch Selected Batches</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($recall->status == 'dispatched')
                <div class="card mb-4">
                    <div class="card-header">Confirm Receipt (Warehouse)</div>
                    <div class="card-body">
                        <form action="{{ route('warehouse.stock-control.recall.receive', $recall) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label>Received Quantity</label>
                                <input type="number" name="received_quantity" class="form-control" max="{{ $recall->dispatched_quantity }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Remarks</label>
                                <textarea name="warehouse_remarks" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Confirm Receipt</button>
                        </form>
                    </div>
                </div>
            @endif

        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Request Summary</div>
                <div class="card-body">
                    <p><strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $recall->reason)) }}</p>
                    <p><strong>Requested:</strong> {{ $recall->requested_quantity }}</p>
                    <p><strong>Approved:</strong> {{ $recall->approved_quantity }}</p>
                    <p><strong>Dispatched:</strong> {{ $recall->dispatched_quantity }}</p>
                    <p><strong>Received:</strong> {{ $recall->received_quantity }}</p>
                    <p><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $recall->status)) }}</p>
                </div>
            </div>
        </div>
    </div>

</div>

</x-app-layout>