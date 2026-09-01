@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Cookbook Builder</h4>
                <div class="page-title-right">
                    <a href="{{ route('kitchen.cookbook.create') }}" class="btn btn-primary">Create New Recipe</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Recipe Name</th>
                                <th>Menu Item</th>
                                <th>Prep Time</th>
                                <th>Cook Time</th>
                                <th>Yield</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recipes as $recipe)
                            <tr>
                                <td>{{ $recipe->name }}</td>
                                <td>{{ $recipe->menuItem->name ?? 'N/A' }}</td>
                                <td>{{ $recipe->prep_time_minutes }} mins</td>
                                <td>{{ $recipe->cook_time_minutes }} mins</td>
                                <td>{{ $recipe->yield_quantity }} {{ $recipe->yield_unit }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info">View Steps</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
