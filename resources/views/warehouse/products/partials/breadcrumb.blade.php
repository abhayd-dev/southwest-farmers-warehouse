<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        {{-- Dashboard --}}
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="mdi mdi-home-outline"></i> Dashboard
            </a>
        </li>

        {{-- Warehouse --}}
        <li class="breadcrumb-item">
            <a href="{{ route('warehouse.index') }}" class="text-decoration-none">
                Warehouse
            </a>
        </li>

        {{-- Products --}}
        @if($title !== 'Products')
            <li class="breadcrumb-item">
                <a href="{{ route('warehouse.products.index') }}" class="text-decoration-none">
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