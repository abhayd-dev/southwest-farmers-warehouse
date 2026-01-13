<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        {{-- Dashboard --}}
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="mdi mdi-home-outline"></i> Dashboard
            </a>
        </li>

        {{-- Categories --}}
        @if($title !== 'Categories')
            <li class="breadcrumb-item">
                <a href="{{ route('warehouse.categories.index') }}" class="text-decoration-none">
                    Categories
                </a>
            </li>
        @endif

        {{-- Current Page --}}
        <li class="breadcrumb-item active" aria-current="page">
            {{ $title }}
        </li>
    </ol>
</nav>