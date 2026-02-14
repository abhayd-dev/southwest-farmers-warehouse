<div class="d-flex align-items-center gap-2 flex-wrap">
    {{-- SEARCH + FILTER --}}
    <form method="GET" action="{{ route('warehouse.product-options.index') }}" class="d-flex flex-grow-1" style="max-width: 600px;">
        <div class="input-group shadow-sm">
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="form-control border-end-0" 
                   placeholder="Search by option name or SKU...">

            <select name="status" class="form-select border-start-0 border-end-0">
                <option value="">All Status</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            {{-- Apply / Search Button --}}
            <button class="btn btn-primary" type="submit" title="Apply Filter">
                <i class="mdi mdi-magnify"></i>
            </button>

            {{-- Reset Button --}}
            <a href="{{ route('warehouse.product-options.index') }}" 
               class="btn btn-outline-secondary" 
               title="Reset Filters">
                <i class="mdi mdi-refresh"></i>
            </a>
        </div>
    </form>

    {{-- ACTION BUTTONS --}}
    <div class="d-flex align-items-center gap-2 ms-auto">
        {{-- EXPORT --}}
        <a href="{{ route('warehouse.product-options.export') }}" 
           class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="mdi mdi-download"></i>
            <span class="d-none d-lg-inline">Export</span>
        </a>

        {{-- IMPORT --}}
        <button class="btn btn-outline-success d-flex align-items-center gap-1" 
                data-bs-toggle="modal" 
                data-bs-target="#importModal">
            <i class="mdi mdi-upload"></i>
            <span class="d-none d-lg-inline">Import</span>
        </button>

        {{-- ADD --}}
        <a href="{{ route('warehouse.product-options.create') }}" 
           class="btn btn-success d-flex align-items-center gap-1">
            <i class="mdi mdi-plus-circle"></i>
            <span class="d-none d-lg-inline">Add Option</span>
        </a>
    </div>
</div>