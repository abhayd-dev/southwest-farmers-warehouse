<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        {{-- Dashboard --}}
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="mdi mdi-home-outline"></i> Dashboard
            </a>
        </li>

        {{-- Subcategories --}}
        @if($title !== 'Subcategories')
            <li class="breadcrumb-item">
                <a href="{{ route('warehouse.subcategories.index') }}" class="text-decoration-none">
                    Subcategories
                </a>
            </li>
        @endif

        {{-- Current Page --}}
        <li class="breadcrumb-item active" aria-current="page">
            {{ $title }}
        </li>
    </ol>
</nav>