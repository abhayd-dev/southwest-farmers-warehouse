<div class="d-flex align-items-center gap-2 flex-wrap">
    {{-- SEARCH + FILTER --}}
    <form method="GET" action="{{ route('warehouse.products.index') }}" class="d-flex flex-grow-1"
        style="max-width: 900px;">
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

        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_products'))
            <button type="button" class="btn btn-warning text-dark d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#pricingModal">
                <i class="mdi mdi-currency-usd text-white"></i>
                <span class="d-none d-lg-inline text-white">Set Pricing</span>
            </button>
        @endif

        {{-- EXPORT --}}
        @if (auth()->user()->isSuperAdmin() ||
                auth()->user()->hasPermission('manage_products') ||
                auth()->user()->hasPermission('export_reports'))
            <a href="{{ route('warehouse.products.export', request()->all()) }}"
                class="btn btn-outline-primary d-flex align-items-center gap-1">
                <i class="mdi mdi-download"></i>
                <span class="d-none d-lg-inline">Export</span>
            </a>
        @endif

        {{-- IMPORT BUTTON --}}
        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_products'))
            <button type="button" class="btn btn-outline-success d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="mdi mdi-upload"></i>
                <span class="d-none d-lg-inline">Import</span>
            </button>
        @endif

        {{-- DELETE ALL / BULK DELETE --}}
        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_products') || auth()->user()->hasPermission('manage_products'))
            <button type="button" id="deleteAllBtn" class="btn btn-outline-danger d-flex align-items-center gap-1"
                title="Delete All / Selected Products">
                <i class="mdi mdi-delete-sweep"></i>
                <span class="d-none d-lg-inline">Delete All</span>
            </button>

            {{-- Hidden form: Delete ALL warehouse products --}}
            <form id="deleteAllForm" action="{{ route('warehouse.products.destroy-all') }}" method="POST"
                class="d-none">
                @csrf
                @method('DELETE')
            </form>

            {{-- Hidden form: Delete SELECTED product IDs --}}
            <form id="deleteBulkForm" action="{{ route('warehouse.products.destroy-bulk') }}" method="POST"
                class="d-none">
                @csrf
                @method('DELETE')
                <div id="bulkIdsContainer"></div>
            </form>
        @endif

        {{-- ADD --}}
        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_products'))
            <a href="{{ route('warehouse.products.create') }}" class="btn btn-success d-flex align-items-center gap-1">
                <i class="mdi mdi-plus-circle"></i>
                <span class="d-none d-lg-inline">Add Product</span>
            </a>
        @endif
    </div>
</div>
