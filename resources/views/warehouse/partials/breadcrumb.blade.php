<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0 bg-white p-3 rounded shadow-sm">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark">
                <i class="mdi mdi-home-outline me-1"></i> Dashboard
            </a>
        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('warehouse.index') }}" class="text-decoration-none text-dark">
                Warehouse
            </a>
        </li>

        @if($title !== 'Stock Control')
            <li class="breadcrumb-item">
                <a href="{{ route('warehouse.stock-control.overview') }}" class="text-decoration-none text-dark">
                    Stock Control
                </a>
            </li>
        @endif

        <li class="breadcrumb-item active fw-bold" aria-current="page">
            {{ $title }}
        </li>
    </ol>
</nav>