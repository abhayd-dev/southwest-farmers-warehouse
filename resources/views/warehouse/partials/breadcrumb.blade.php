@props(['title', 'items' => []])

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0 bg-white p-3 rounded shadow-sm">
        
        {{-- 1. Fixed Dashboard Link --}}
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark">
                <i class="mdi mdi-home-outline me-1"></i> Dashboard
            </a>
        </li>

        {{-- 2. Dynamic Middle Links (Passed via array) --}}
        @foreach($items as $item)
            <li class="breadcrumb-item">
                @if(isset($item['url']) && $item['url'] != '#')
                    <a href="{{ $item['url'] }}" class="text-decoration-none text-dark">
                        {{ $item['text'] }}
                    </a>
                @else
                    <span class="text-muted">{{ $item['text'] }}</span>
                @endif
            </li>
        @endforeach

        {{-- 3. Active Page Title --}}
        <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">
            {{ $title }}
        </li>
    </ol>
</nav>