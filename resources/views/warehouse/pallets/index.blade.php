<x-app-layout title="Pallet Builder">
    <div class="container-fluid p-3 p-md-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-dolly text-primary"></i> Pallet Builder
                </h4>
                <small class="text-muted">Organize dispatch items onto pallets (Max 2,200 lbs each)</small>
            </div>
            <a href="{{ route('warehouse.pallets.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> New Pallet
            </a>
        </div>

        {{-- STATUS TABS --}}
        <ul class="nav nav-tabs mb-4">
            @foreach(['preparing' => ['label' => 'Preparing', 'count' => $preparingCount, 'color' => 'warning'],
                      'ready'     => ['label' => 'Ready',     'count' => $readyCount,     'color' => 'success'],
                      'in_transit'=> ['label' => 'In Transit','count' => $inTransitCount, 'color' => 'info'],
                      'delivered' => ['label' => 'Delivered', 'count' => $deliveredCount, 'color' => 'secondary'],
                      'all'       => ['label' => 'All',       'count' => null,             'color' => 'dark']] as $key => $tab)
                <li class="nav-item">
                    <a href="{{ route('warehouse.pallets.index', ['status' => $key]) }}"
                       class="nav-link {{ $status === $key ? 'active fw-bold' : '' }}">
                        {{ $tab['label'] }}
                        @if($tab['count'] !== null && $tab['count'] > 0)
                            <span class="badge bg-{{ $tab['color'] }} ms-1">{{ $tab['count'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- PALLETS GRID --}}
        <div class="row g-3">
            @forelse($pallets as $pallet)
                @php
                    $pct = $pallet->max_weight > 0 ? min(100, round(($pallet->total_weight / $pallet->max_weight) * 100)) : 0;
                    $barColor = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="fw-bold fs-6">{{ $pallet->pallet_number }}</div>
                                    <small class="text-muted">
                                        {{ $pallet->department->name ?? 'No Dept' }}
                                        @if($pallet->storePO)
                                            &bull; PO: {{ $pallet->storePO->po_number }}
                                        @endif
                                    </small>
                                </div>
                                <span class="badge bg-{{ $pallet->status === 'preparing' ? 'warning text-dark' : ($pallet->status === 'ready' ? 'success' : ($pallet->status === 'in_transit' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $pallet->status)) }}
                                </span>
                            </div>

                            {{-- Weight Bar --}}
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>{{ number_format($pallet->total_weight, 1) }} lbs</span>
                                    <span>Max: {{ number_format($pallet->max_weight, 0) }} lbs</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="small text-muted mt-1">{{ $pct }}% full &bull; {{ number_format($pallet->remainingCapacity(), 1) }} lbs remaining</div>
                            </div>

                            <div class="small text-muted mb-3">
                                <i class="mdi mdi-package-variant me-1"></i> {{ $pallet->items->count() }} item type(s)
                            </div>

                            <div class="action-btns w-100">
                                <a href="{{ route('warehouse.pallets.show', $pallet->id) }}" class="btn btn-sm btn-outline-info flex-grow-1" title="Build / View">
                                    <i class="mdi mdi-pencil me-1"></i> Build
                                </a>
                                <a href="{{ route('warehouse.pallets.manifest', $pallet->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print Manifest">
                                    <i class="mdi mdi-printer"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="mdi mdi-dolly text-muted opacity-25" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-2 fw-medium">No pallets found for this status.</p>
                            <a href="{{ route('warehouse.pallets.create') }}" class="btn btn-primary btn-sm px-4">
                                <i class="mdi mdi-plus me-1"></i> Create First Pallet
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($pallets->hasPages())
            <div class="mt-4">{{ $pallets->links() }}</div>
        @endif

    </div>
</x-app-layout>
