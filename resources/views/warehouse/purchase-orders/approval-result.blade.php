@extends('warehouse.layouts.app')

@section('title', 'PO Approval Result')

@section('content')
<div class="container-xl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-body text-center py-5">
                    @if($success)
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-success" width="64" height="64" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M9 12l2 2l4 -4"></path>
                            </svg>
                        </div>
                        <h2 class="text-success">Success!</h2>
                        <p class="text-muted mt-3">{{ $message }}</p>
                        
                        @if(isset($po))
                        <div class="mt-4">
                            <p><strong>PO Number:</strong> {{ $po->po_number }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $po->isApproved() ? 'success' : 'danger' }}">
                                    {{ ucfirst($po->approval_status) }}
                                </span>
                            </p>
                        </div>
                        @endif
                    @else
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-danger" width="64" height="64" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M10 10l4 4m0 -4l-4 4"></path>
                            </svg>
                        </div>
                        <h2 class="text-danger">Error</h2>
                        <p class="text-muted mt-3">{{ $message }}</p>
                    @endif

                    <div class="mt-4">
                        <p class="text-muted">You can close this window now.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
