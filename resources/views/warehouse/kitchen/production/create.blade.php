@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="mb-sm-0">Log New Production</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('kitchen.production.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Menu Item *</label>
                                <select name="menu_item_id" class="form-control" required>
                                    <option value="">-- Select Item --</option>
                                    @foreach($menuItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kitchen Location *</label>
                                <select name="kitchen_location_id" class="form-control" required>
                                    <option value="">-- Select Kitchen --</option>
                                    @foreach($kitchenLocations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Prepared By</label>
                                <select name="ware_user_id" class="form-control">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($staff as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time of Production *</label>
                                <input type="datetime-local" name="produced_at" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Quantity Made *</label>
                                <input type="number" name="quantity_made" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit</label>
                                <select name="quantity_unit" class="form-control">
                                    <option value="batch">Batch</option>
                                    <option value="kg">KG</option>
                                    <option value="litres">Litres</option>
                                    <option value="trays">Trays</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Yield (Plates / Portions)</label>
                                <input type="number" name="yield_plates" class="form-control" step="0.01">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Today's Target Quantity</label>
                                <input type="number" name="daily_target" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Save Production Log</button>
                            <a href="{{ route('kitchen.production.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
