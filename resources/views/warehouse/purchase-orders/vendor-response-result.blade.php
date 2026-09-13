<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Response</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f4f6f9; }
        .card { border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .icon-lg { width: 70px; height: 70px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-7 col-lg-6">
            <div class="card">
                <div class="card-body text-center py-5">

                    @if($success)
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-lg text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                                <path stroke-width="2" d="M9 12l2 2l4 -4"></path>
                            </svg>
                        </div>

                        <h2 class="text-success fw-bold">Thank You</h2>
                        <p class="text-muted mt-3">{{ $message }}</p>

                        @if(isset($po))
                        <div class="mt-4 text-start border-top pt-3">
                            <p><strong>PO Number:</strong> {{ $po->po_number }}</p>
                            <p><strong>Your Response:</strong>
                                <span class="badge bg-{{ $po->isVendorAcknowledged() ? 'success' : 'danger' }}">
                                    {{ ucfirst($po->vendor_response_status) }}
                                </span>
                            </p>
                        </div>
                        @endif
                    @else
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-lg text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                                <path stroke-width="2" d="M10 10l4 4m0 -4l-4 4"></path>
                            </svg>
                        </div>

                        <h2 class="text-danger fw-bold">Error</h2>
                        <p class="text-muted mt-3">{{ $message }}</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
