<div class="d-flex align-items-center gap-2 flex-wrap">
    {{-- SEARCH + FILTER --}}
    <form method="GET" action="{{ route('warehouse.products.index') }}" class="d-flex flex-grow-1"
        style="max-width: 600px;">
        <div class="input-group shadow-sm">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-end-0"
                placeholder="Search by product name, SKU or Barcode...">

            <select name="status" class="form-select border-start-0 border-end-0">
                <option value="">All Status</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button class="btn btn-primary" type="submit">
                <i class="mdi mdi-magnify"></i>
            </button>

            @if (request('search') || request()->has('status'))
                <a href="{{ route('warehouse.products.index') }}" class="btn btn-outline-secondary"
                    title="Clear Filters">
                    <i class="mdi mdi-close"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- ACTION BUTTONS --}}
    <div class="d-flex align-items-center gap-2 ms-auto">
        {{-- EXPORT --}}
        <a href="{{ route('warehouse.products.export') }}"
            class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="mdi mdi-download"></i>
            <span class="d-none d-lg-inline">Export</span>
        </a>

        {{-- IMPORT BUTTON --}}
        {{-- Fixed: Using standard Bootstrap 5 Data Attributes. No JS required to open. --}}
        <button type="button" class="btn btn-outline-success d-flex align-items-center gap-1"
            data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="mdi mdi-upload"></i>
            <span class="d-none d-lg-inline">Import</span>
        </button>

        {{-- ADD --}}
        <a href="{{ route('warehouse.products.create') }}" class="btn btn-success d-flex align-items-center gap-1">
            <i class="mdi mdi-plus-circle"></i>
            <span class="d-none d-lg-inline">Add Product</span>
        </a>
    </div>
</div>