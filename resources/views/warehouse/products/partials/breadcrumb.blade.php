<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        {{-- Dashboard --}}
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="page-breadcrumb-link">
                <i class="mdi mdi-home-outline"></i> Dashboard
            </a>
        </li>

        {{-- Warehouse --}}
        <li class="breadcrumb-item">
            <a href="{{ route('warehouse.index') }}" class="page-breadcrumb-link">
                Warehouse
            </a>
        </li>

        {{-- Products --}}
        @if($title !== 'Products')
            <li class="breadcrumb-item">
                <a href="{{ route('warehouse.products.index') }}" class="page-breadcrumb-link">
                    Products
                </a>
            </li>
        @endif

        {{-- Current Page --}}
        <li class="breadcrumb-item active" aria-current="page">
            {{ $title }}
        </li>
    </ol>
</nav>