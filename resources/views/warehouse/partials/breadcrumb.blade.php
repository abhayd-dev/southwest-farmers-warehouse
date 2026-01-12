<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        {{-- Dashboard --}}
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="mdi mdi-home-outline"></i> Dashboard
            </a>
        </li>

        {{-- Parent --}}
        @isset($parent)
            <li class="breadcrumb-item">
                @if(isset($parentRoute))
                    <a href="{{ route($parentRoute) }}" class="text-decoration-none">
                        {{ $parent }}
                    </a>
                @else
                    {{ $parent }}
                @endif
            </li>
        @endisset

        {{-- Current --}}
        <li class="breadcrumb-item active" aria-current="page">
            {{ $title }}
        </li>
    </ol>
</nav>