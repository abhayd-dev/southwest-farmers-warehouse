@extends('layouts.app')

@section('title', 'Cost Increase Approval - PO #' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="fw-bold mb-0"><i class="mdi mdi-alert-circle-outline me-2"></i> Action Required: Cost Increase Approval</h5>
                </div>
                <div class="card-body p-4">
                    <p class="fs-5">The receiving process for <strong>PO #{{ $purchaseOrder->po_number }}</strong> from <strong>{{ $purchaseOrder->vendor->name ?? 'Vendor' }}</strong> has been paused.</p>
                    
                    <p>One or more items in this order will result in a <strong>True Cost</strong> (including shipping, duties, taxes, demurrage, etc.) that is higher than the product's current cost in the catalog.</p>
                    
                    <div class="alert alert-info mt-4">
                        <h6 class="fw-bold"><i class="mdi mdi-information me-1"></i> What does this mean?</h6>
                        If you approve this, the receiving process can proceed, and the product's cost price in the catalog will be automatically updated to the new, higher True Cost once the stock is received.
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('warehouse.receiving.show', $purchaseOrder->id) }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Receiving
                        </a>
                        
                        <form action="{{ route('warehouse.purchase-orders.approve-cost', $purchaseOrder->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                                <i class="mdi mdi-check-decagram me-1"></i> Approve Cost Increase
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
