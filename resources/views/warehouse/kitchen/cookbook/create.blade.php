@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Create New Recipe & Timers</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Recipe Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Target Menu Item</label>
                                <select name="menu_item_id" class="form-control" required>
                                    @foreach($menuItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h5>Step-by-Step Instructions (With Timers)</h5>
                        <div id="steps-container">
                            <div class="row mb-2">
                                <div class="col-md-1">
                                    <input type="number" class="form-control" placeholder="Step" value="1" disabled>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" placeholder="Instruction (e.g. Boil water and add ingredients)">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" placeholder="Timer (Minutes)">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2">+ Add Step</button>

                        <hr>
                        <button type="submit" class="btn btn-primary mt-3">Save Recipe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
