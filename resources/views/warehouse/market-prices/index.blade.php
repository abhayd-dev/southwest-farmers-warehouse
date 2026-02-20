<x-app-layout title="Market-Level Pricing">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Market-Level Pricing',
            'items' => []
        ])

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('warehouse.market-prices.index') }}" method="GET" class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Select Market to manage prices:</label>
                                <select name="market_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Choose Market --</option>
                                    @foreach($markets as $market)
                                        <option value="{{ $market->id }}" {{ ($selectedMarket && $selectedMarket->id == $market->id) ? 'selected' : '' }}>
                                            {{ $market->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedMarket)
                <div class="card border-0 shadow-sm">
                    <form action="{{ route('warehouse.market-prices.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="market_id" value="{{ $selectedMarket->id }}">
                        
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">Pricing for {{ $selectedMarket->name }}</h5>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-content-save me-1"></i>Save All Prices
                            </button>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>SKU</th>
                                            <th>Product Name</th>
                                            <th>Default Cost</th>
                                            <th>Default Price</th>
                                            <th style="width: 150px;">Market Cost Price</th>
                                            <th style="width: 150px;">Market Selling Price</th>
                                            <th style="width: 120px;">Promotion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($products as $product)
                                            @php
                                                $marketPrice = $product->marketPrices->first();
                                                $costPrice = $marketPrice ? $marketPrice->cost_price : $product->cost_price;
                                                $salePrice = $marketPrice ? $marketPrice->sale_price : $product->price;
                                                
                                                $hasPromo = $marketPrice && $marketPrice->promotion_price > 0 && 
                                                           $marketPrice->promotion_start_date && $marketPrice->promotion_end_date && 
                                                           now()->between($marketPrice->promotion_start_date, $marketPrice->promotion_end_date);
                                            @endphp
                                            <tr>
                                                <td><span class="badge bg-secondary">{{ $product->sku }}</span></td>
                                                <td class="fw-semibold">
                                                    {{ $product->product_name }}
                                                    @if($hasPromo)
                                                        <br><span class="badge bg-danger rounded-pill"><i class="mdi mdi-tag"></i> Active Promo: ${{ number_format($marketPrice->promotion_price, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>${{ number_format($product->cost_price, 2) }}</td>
                                                <td>${{ number_format($product->price, 2) }}</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" step="0.01" min="0" 
                                                               name="prices[{{ $product->id }}][cost_price]" 
                                                               class="form-control" 
                                                               value="{{ number_format($costPrice, 2, '.', '') }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" step="0.01" min="0" 
                                                               name="prices[{{ $product->id }}][sale_price]" 
                                                               class="form-control" 
                                                               value="{{ number_format($salePrice, 2, '.', '') }}">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            onclick="openPromoModal({{ $product->id }}, '{{ addslashes($product->product_name) }}', '{{ $marketPrice->promotion_price ?? '' }}', '{{ $marketPrice && $marketPrice->promotion_start_date ? $marketPrice->promotion_start_date->format('Y-m-d\TH:i') : '' }}', '{{ $marketPrice && $marketPrice->promotion_end_date ? $marketPrice->promotion_end_date->format('Y-m-d\TH:i') : '' }}')">
                                                        <i class="mdi mdi-tag-plus"></i> Promo
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No products found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        @if($products->count() > 0)
                        <div class="card-footer bg-white text-end">
                            <button type="submit" class="btn btn-primary">Save All Prices</button>
                        </div>
                        @endif
                    </form>
                </div>

                {{-- Promo Modal --}}
                <div class="modal fade" id="promoModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('warehouse.market-prices.promo') }}" method="POST">
                                @csrf
                                <input type="hidden" name="market_id" value="{{ $selectedMarket->id }}">
                                <input type="hidden" name="product_id" id="promo_product_id">
                                
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title fw-bold text-dark"><i class="mdi mdi-tag me-2"></i>Set Promotion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-3 text-muted">Setting promotion for <strong id="promo_product_name" class="text-dark"></strong> in {{ $selectedMarket->name }}.</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Promotional Price ($)</label>
                                        <input type="number" step="0.01" min="0" name="promotion_price" id="promo_price" class="form-control" placeholder="0.00">
                                        <small class="text-muted">Leave blank to remove overide.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Start Date & Time</label>
                                        <input type="datetime-local" name="promotion_start_date" id="promo_start" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">End Date & Time</label>
                                        <input type="datetime-local" name="promotion_end_date" id="promo_end" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Promotion</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                    function openPromoModal(productId, productName, promoPrice, promoStart, promoEnd) {
                        document.getElementById('promo_product_id').value = productId;
                        document.getElementById('promo_product_name').innerText = productName;
                        document.getElementById('promo_price').value = promoPrice;
                        document.getElementById('promo_start').value = promoStart;
                        document.getElementById('promo_end').value = promoEnd;
                        new bootstrap.Modal(document.getElementById('promoModal')).show();
                    }
                </script>
                @endpush

                @endif

            </div>
        </div>
    </div>
</x-app-layout>
