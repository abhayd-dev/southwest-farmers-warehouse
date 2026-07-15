<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Request</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #f4f6f9;
        }
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .icon-lg {
            width: 70px;
            height: 70px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-7 col-lg-6">
            <div class="card">
                <div class="card-body text-center py-5">

                    {{-- SUCCESS BLOCK --}}
                    @if($success)

                        <div class="mb-4">
                            <!-- Success Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-lg text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                                <path stroke-width="2" d="M9 12l2 2l4 -4"></path>
                            </svg>
                        </div>

                        <h2 class="text-success fw-bold">Success!</h2>
                        <p class="text-muted mt-3">{{ $message }}</p>

                        @if(isset($po))
                        <div class="mt-4 text-start border-top pt-3">
                            <p><strong>PO Number:</strong> {{ $po->po_number }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $po->isApproved() ? 'success' : 'danger' }}">
                                    {{ ucfirst($po->approval_status) }}
                                </span>
                            </p>
                        </div>
                        @endif

                    {{-- ERROR BLOCK --}}
                    @else

                        <div class="mb-4">
                            <!-- Error Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-lg text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                                <path stroke-width="2" d="M10 10l4 4m0 -4l-4 4"></path>
                            </svg>
                        </div>

                        <h2 class="text-danger fw-bold">Error</h2>
                        <p class="text-muted mt-3">{{ $message }}</p>

                    @endif

                    <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                        @if(isset($po))
                            <a href="{{ route('warehouse.purchase-orders.show', $po->id) }}"
                               class="btn btn-{{ $success ? 'success' : 'danger' }}">
                                ← Back to Purchase Order
                            </a>
                            <a href="{{ route('warehouse.purchase-orders.show', $po->id) }}"
                               class="btn btn-outline-secondary"
                               onclick="window.location.reload(); return false;">
                                ↺ Reload
                            </a>
                        @else
                            <button onclick="history.back()" class="btn btn-{{ $success ? 'success' : 'danger' }}">
                                ← Go Back
                            </button>
                            <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                                ↺ Reload
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>