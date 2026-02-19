@extends('warehouse.layouts.app')

@section('title', 'Reject Purchase Order')

@section('content')
<div class="container-xl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="card-title">Reject Purchase Order</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4 class="alert-title">Confirm Rejection</h4>
                        <p>You are about to reject Purchase Order <strong>{{ $purchaseOrder->po_number }}</strong>.</p>
                        <p>Please provide a reason for rejection:</p>
                    </div>

                    <form method="GET" action="{{ route('warehouse.purchase-orders.approve', $purchaseOrder) }}">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="mb-3">
                            <label class="form-label required">Rejection Reason</label>
                            <textarea 
                                name="reason" 
                                class="form-control" 
                                rows="4" 
                                required 
                                placeholder="Please explain why you are rejecting this purchase order..."
                            ></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Confirm Rejection
                            </button>
                            <a href="javascript:window.close()" class="btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
