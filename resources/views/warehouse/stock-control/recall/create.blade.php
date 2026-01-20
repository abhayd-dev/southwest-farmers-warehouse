<x-app-layout title="Initiate Recall Request">

<div class="container-fluid">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Initiate New Recall Request</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('warehouse.stock-control.recall.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Store</label>
                        <select name="store_id" class="form-select" required>
                            <option value="">Select Store</option>
                            @foreach(\App\Models\StoreDetail::all() as $store)
                                <option value="{{ $store->id }}">{{ $store->store_name }} ({{ $store->city }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach(\App\Models\Product::all() as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Requested Quantity</label>
                    <input type="number" name="requested_quantity" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <select name="reason" class="form-select" required>
                        <option value="near_expiry">Near Expiry</option>
                        <option value="quality_issue">Quality Issue</option>
                        <option value="overstock">Overstock</option>
                        <option value="damage">Damage</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea name="reason_remarks" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Recall Request</button>
                    <a href="{{ route('warehouse.stock-control.recall.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

</x-app-layout>