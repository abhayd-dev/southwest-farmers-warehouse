@props(['icon' => null, 'title', 'description' => null, 'breadcrumbs' => []])

<div class="bg-white border-bottom shadow-sm mb-4">
    <div class="py-3 container-fluid">
        @if (count($breadcrumbs))
            <nav class="mb-2">
                <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                    @foreach ($breadcrumbs as $i => $crumb)
                        @if ($i === count($breadcrumbs) - 1 || empty($crumb['url']))
                            <li class="breadcrumb-item text-muted">{{ $crumb['label'] }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ $crumb['url'] }}" class="page-breadcrumb-link">{{ $crumb['label'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        <h4 class="fw-bold mb-0 text-dark">
            @if ($icon)<i class="{{ $icon }} me-1"></i>@endif
            {{ $title }}
        </h4>
        @if ($description)
            <p class="text-muted small mb-0 mt-1">{{ $description }}</p>
        @endif
    </div>
</div>

<style>
    .page-breadcrumb-link { color: #0d6efd; text-decoration: none; }
    .page-breadcrumb-link:hover { color: #0d6efd; font-weight: 700; text-decoration: underline; }
</style>
