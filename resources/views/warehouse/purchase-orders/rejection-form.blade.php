<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Purchase Order</title>

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
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-7 col-lg-6">
            <div class="card">
                
                <div class="card-header bg-danger text-white text-center">
                    <h3 class="mb-0">Reject Purchase Order</h3>
                </div>

                <div class="card-body">

                    <div class="alert alert-warning">
                        <h5 class="alert-heading">Confirm Rejection</h5>
                        <p class="mb-2">
                            You are about to reject Purchase Order 
                            <strong>{{ $purchaseOrder->po_number }}</strong>.
                        </p>
                        <p class="mb-0">Please provide a reason for rejection:</p>
                    </div>

                   <form method="GET" action="{{ route('warehouse.purchase-orders.approve', $purchaseOrder) }}">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="expires" value="{{ request('expires') }}">
                        <input type="hidden" name="signature" value="{{ request('signature') }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea 
                                name="reason"
                                class="form-control"
                                rows="4"
                                required
                                placeholder="Please explain why you are rejecting this purchase order..."
                            ></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-danger px-4">
                                ❌ Confirm Rejection
                            </button>

                            <a href="javascript:window.close()" class="btn btn-outline-secondary px-4">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>