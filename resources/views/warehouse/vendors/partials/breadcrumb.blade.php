<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="page-breadcrumb-link">
                <i class="mdi mdi-home-outline"></i> Dashboard
            </a>
        </li>
        @if($title !== 'Vendors' && $title !== 'All Vendors')
            <li class="breadcrumb-item">
                <a href="{{ route('warehouse.vendors.index') }}" class="page-breadcrumb-link">
                    Vendors
                </a>
            </li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">
            {{ $title === 'All Vendors' ? 'Vendors' : $title }}
        </li>
    </ol>
</nav>