<x-app-layout title="Edit Warehouse">
    
    {{-- Leaflet CSS --}}
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            #map { 
                height: 450px; 
                width: 100%; 
                border-radius: 0.5rem;
                z-index: 1;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }
            .search-results {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1000;
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                max-height: 250px;
                overflow-y: auto;
                display: none;
                margin-top: 0.25rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }
            .search-result-item {
                padding: 0.75rem 1rem;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: all 0.2s ease;
            }
            .search-result-item:last-child {
                border-bottom: none;
            }
            .search-result-item:hover {
                background-color: #f8f9fa;
                padding-left: 1.25rem;
            }
            .search-result-item i {
                color: #6c757d;
            }
        </style>
    @endpush

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                    <i class="mdi mdi-home-outline"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('warehouse.index') }}" class="text-decoration-none">
                                    Warehouse
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Edit Warehouse
                            </li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i>
                        Edit Warehouse
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.update', $warehouse) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                
                {{-- LEFT COLUMN: Basic Info --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="mdi mdi-information text-primary"></i>
                                Basic Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                
                                {{-- WAREHOUSE NAME --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Warehouse Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-warehouse text-muted"></i>
                                        </span>
                                        <input type="text" name="warehouse_name" class="form-control border-start-0" 
                                               value="{{ old('warehouse_name', $warehouse->warehouse_name) }}"
                                               placeholder="Enter warehouse name" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a warehouse name.</div>
                                </div>
                                
                                {{-- CODE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Code <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-barcode text-muted"></i>
                                        </span>
                                        <input type="text" name="code" class="form-control border-start-0" 
                                               value="{{ old('code', $warehouse->code) }}"
                                               placeholder="Warehouse code" required>
                                    </div>
                                    <div class="invalid-feedback">Code is required.</div>
                                </div>

                                {{-- EMAIL --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-email-outline text-muted"></i>
                                        </span>
                                        <input type="email" name="email" class="form-control border-start-0" 
                                               value="{{ old('email', $warehouse->email) }}"
                                               placeholder="warehouse@example.com">
                                    </div>
                                </div>

                                {{-- PHONE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-phone-outline text-muted"></i>
                                        </span>
                                        <input type="text" name="phone" class="form-control border-start-0" 
                                               value="{{ old('phone', $warehouse->phone) }}"
                                               placeholder="+1 234 567 8900">
                                    </div>
                                </div>

                                {{-- ADDRESS --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 align-items-start pt-2">
                                            <i class="mdi mdi-map-marker text-muted"></i>
                                        </span>
                                        <textarea name="address" class="form-control border-start-0" 
                                                  rows="2" placeholder="Enter complete address">{{ old('address', $warehouse->address) }}</textarea>
                                    </div>
                                </div>
                                
                                {{-- CITY --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-city text-muted"></i>
                                        </span>
                                        <input type="text" name="city" class="form-control border-start-0" 
                                               value="{{ old('city', $warehouse->city) }}"
                                               placeholder="Enter city">
                                    </div>
                                </div>

                                {{-- STATE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">State</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-map text-muted"></i>
                                        </span>
                                        <input type="text" name="state" class="form-control border-start-0" 
                                               value="{{ old('state', $warehouse->state) }}"
                                               placeholder="Enter state">
                                    </div>
                                </div>

                                {{-- COUNTRY --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-earth text-muted"></i>
                                        </span>
                                        <input type="text" name="country" class="form-control border-start-0" 
                                               value="{{ old('country', $warehouse->country) }}"
                                               placeholder="Enter country">
                                    </div>
                                </div>

                                {{-- PINCODE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Pincode</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-mailbox text-muted"></i>
                                        </span>
                                        <input type="text" name="pincode" class="form-control border-start-0" 
                                               value="{{ old('pincode', $warehouse->pincode) }}"
                                               placeholder="Enter pincode">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Map & Coordinates --}}
                <div class="col-lg-6">
                    
                    {{-- LOCATION CARD --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="mdi mdi-map-marker text-danger"></i>
                                Location Mapping
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            
                            {{-- INFO ALERT --}}
                            <div class="alert alert-info d-flex align-items-start mb-4 border-0">
                                <i class="mdi mdi-information-outline fs-5 me-2"></i>
                                <div class="small">
                                    <strong>Tip:</strong> Search for location or click/drag marker on map to set coordinates.
                                </div>
                            </div>

                            {{-- SEARCH BOX --}}
                            <div class="mb-3 position-relative">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-magnify text-primary"></i>
                                    Search Location
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="mdi mdi-map-search text-muted"></i>
                                    </span>
                                    <input type="text" id="locationSearch" 
                                           class="form-control border-start-0" 
                                           placeholder="Type area name (e.g., Connaught Place, Delhi)..." 
                                           autocomplete="off">
                                </div>
                                
                                {{-- LOADING INDICATOR --}}
                                <div id="searchLoader" class="position-absolute end-0 d-none" 
                                     style="top: 2.75rem; right: 0.75rem; z-index: 5;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                {{-- SEARCH RESULTS --}}
                                <div id="searchResults" class="search-results"></div>
                            </div>

                            {{-- MAP CONTAINER --}}
                            <div id="map" class="mb-3"></div>

                            {{-- COORDINATES DISPLAY --}}
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small text-muted">
                                        <i class="mdi mdi-latitude"></i> Latitude
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">
                                            <i class="mdi mdi-map-marker-radius text-muted"></i>
                                        </span>
                                        <input type="text" id="latitudeDisplay" 
                                               class="form-control bg-light" 
                                               value="{{ $warehouse->latitude ?? '28.6139' }}" 
                                               readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small text-muted">
                                        <i class="mdi mdi-longitude"></i> Longitude
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">
                                            <i class="mdi mdi-map-marker-radius text-muted"></i>
                                        </span>
                                        <input type="text" id="longitudeDisplay" 
                                               class="form-control bg-light" 
                                               value="{{ $warehouse->longitude ?? '77.2090' }}" 
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- HIDDEN INPUTS --}}
                            <input type="hidden" name="latitude" id="latitude" 
                                   value="{{ old('latitude', $warehouse->latitude) }}">
                            <input type="hidden" name="longitude" id="longitude" 
                                   value="{{ old('longitude', $warehouse->longitude) }}">

                        </div>
                    </div>

                    {{-- WAREHOUSE INFO --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="mdi mdi-warehouse"></i>
                                Warehouse Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Status:</small>
                                    <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'warning' }}">
                                        {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Created:</small>
                                    <small class="fw-semibold">{{ $warehouse->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Last Updated:</small>
                                    <small class="fw-semibold">{{ $warehouse->updated_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg text-white">
                                    <i class="mdi mdi-content-save me-1"></i>
                                    Update Warehouse
                                </button>
                                <a href="{{ route('warehouse.index') }}" class="btn btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left me-1"></i>
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- LEAFLET SCRIPTS --}}
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                
                // 1. Initial Coordinates
                let lat = parseFloat("{{ $warehouse->latitude }}") || 28.6139;
                let lng = parseFloat("{{ $warehouse->longitude }}") || 77.2090;

                // 2. Initialize Map
                const map = L.map('map').setView([lat, lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                // 3. Add Draggable Marker
                let marker = L.marker([lat, lng], {draggable: true}).addTo(map);

                // Helper to update inputs
                const updateInputs = (lat, lng) => {
                    const latInput = document.getElementById('latitude');
                    const lngInput = document.getElementById('longitude');
                    const latDisplay = document.getElementById('latitudeDisplay');
                    const lngDisplay = document.getElementById('longitudeDisplay');
                    
                    const latFixed = lat.toFixed(7);
                    const lngFixed = lng.toFixed(7);
                    
                    if(latInput) latInput.value = latFixed;
                    if(lngInput) lngInput.value = lngFixed;
                    if(latDisplay) latDisplay.value = latFixed;
                    if(lngDisplay) lngDisplay.value = lngFixed;
                };

                // Update on marker drag
                marker.on('dragend', function(e) {
                    const pos = e.target.getLatLng();
                    updateInputs(pos.lat, pos.lng);
                });

                // Update on map click
                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    updateInputs(e.latlng.lat, e.latlng.lng);
                });

                /* ===============================
                   AUTO SEARCH (DEBOUNCE)
                =============================== */
                const searchInput = document.getElementById('locationSearch');
                const resultsBox = document.getElementById('searchResults');
                const loader = document.getElementById('searchLoader');
                let debounceTimer;

                const performSearch = (query) => {
                    if (!query || query.length < 3) {
                        resultsBox.style.display = 'none';
                        return;
                    }

                    // Show loader
                    loader.classList.remove('d-none');
                    
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            resultsBox.innerHTML = '';
                            if(data.length > 0) {
                                resultsBox.style.display = 'block';
                                data.forEach(place => {
                                    const item = document.createElement('div');
                                    item.className = 'search-result-item';
                                    item.innerHTML = `<i class="mdi mdi-map-marker me-2"></i>${place.display_name}`;
                                    item.onclick = () => {
                                        const newLat = parseFloat(place.lat);
                                        const newLon = parseFloat(place.lon);
                                        
                                        map.setView([newLat, newLon], 15);
                                        marker.setLatLng([newLat, newLon]);
                                        updateInputs(newLat, newLon);
                                        
                                        resultsBox.style.display = 'none';
                                        searchInput.value = place.display_name;
                                    };
                                    resultsBox.appendChild(item);
                                });
                            } else {
                                resultsBox.innerHTML = '<div class="search-result-item text-muted">No results found</div>';
                                resultsBox.style.display = 'block';
                            }
                        })
                        .catch(err => {
                            console.error('Search error:', err);
                            resultsBox.innerHTML = '<div class="search-result-item text-danger"><i class="mdi mdi-alert me-2"></i>Error searching location</div>';
                            resultsBox.style.display = 'block';
                        })
                        .finally(() => {
                            loader.classList.add('d-none');
                        });
                };

                // Listen for Input with Debounce (Wait 800ms after typing stops)
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value;
                    
                    debounceTimer = setTimeout(() => {
                        performSearch(query);
                    }, 800); 
                });

                // Close search results on outside click
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                        resultsBox.style.display = 'none';
                    }
                });

                // Initialize coordinates display
                updateInputs(lat, lng);

            });
        </script>
    @endpush

</x-app-layout>