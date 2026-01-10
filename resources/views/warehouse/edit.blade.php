<x-app-layout title="Edit Warehouse">

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('warehouse.update', $warehouse) }}" class="needs-validation"
                    novalidate>

                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                            <input type="text" name="warehouse_name" class="form-control" required
                                value="{{ $warehouse->warehouse_name }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required
                                value="{{ $warehouse->code }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $warehouse->email }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $warehouse->phone }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control">{{ $warehouse->address }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ $warehouse->city }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ $warehouse->state }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ $warehouse->country }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ $warehouse->pincode }}">
                        </div>

                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary">Update Warehouse</button>
                        <a href="{{ route('warehouse.index') }}" class="btn btn-light">Back</a>
                    </div>

                </form>

            </div>
        </div>
    </div>

</x-app-layout>
