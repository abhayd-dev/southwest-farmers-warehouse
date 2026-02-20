<x-app-layout title="Add Store">
    {{-- LEAFLET CSS --}}
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .search-results {
            position: absolute;
            background: white;
            width: 100%;
            z-index: 1000;
            border: 1px solid #dee2e6;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            border-radius: 0 0 0.375rem 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .search-result-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s;
        }
        .search-result-item:hover {
            background-color: #f8f9fa;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        #map {
            z-index: 1;
        }
    </style>
    @endpush

    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        {{-- BREADCRUMB --}}
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="mdi mdi-home-outline"></i> Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('warehouse.stores.index') }}" class="text-decoration-none">
                                        All Stores
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Register Store
                                </li>
                            </ol>
                        </nav>
                        
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-store-plus text-primary"></i> Register New Store
                        </h4>
                    </div>
                    
                    <a href="{{ route('warehouse.stores.index') }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('warehouse.stores.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            
            <div class="row g-4">
                {{-- LEFT COLUMN: STORE DETAILS --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title text-dark mb-0 fw-bold">
                                <i class="mdi mdi-map-marker-radius text-danger me-2"></i> Store Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Store Name --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Store Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="mdi mdi-store"></i></span>
                                        <input type="text" name="store_name" class="form-control" placeholder="e.g. Southwest Farmers Lucknow" value="{{ old('store_name') }}" required>
                                    </div>
                                </div>

                                {{-- Email & Phone --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Store Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="mdi mdi-email-outline"></i></span>
                                        <input type="email" name="store_email" class="form-control" placeholder="store@example.com" value="{{ old('store_email') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="mdi mdi-phone"></i></span>
                                        <input type="text" name="store_phone" class="form-control" placeholder="+91 98765..." value="{{ old('store_phone') }}" required>
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Full Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light align-items-start pt-2"><i class="mdi mdi-map-marker"></i></span>
                                        <textarea name="address" class="form-control" rows="2" placeholder="Building No, Street..." required>{{ old('address') }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" placeholder="City" value="{{ old('city') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control" placeholder="State" value="{{ old('state') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" placeholder="12345" value="{{ old('pincode') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Market Zone</label>
                                    <select name="market_id" class="form-select">
                                        <option value="">-- Select Market --</option>
                                        @foreach($markets as $market)
                                            <option value="{{ $market->id }}" {{ old('market_id') == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                {{-- MAP SECTION --}}
                                <div class="col-12 mt-4">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <label class="form-label fw-bold mb-2">
                                                <i class="mdi mdi-crosshairs-gps text-primary me-1"></i> Pin Location on Map
                                            </label>
                                            
                                            {{-- Search Box --}}
                                            <div class="position-relative mb-3">
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="mdi mdi-magnify"></i></span>
                                                    <input type="text" id="locationSearch" class="form-control border-start-0" placeholder="Search for a location..." autocomplete="off">
                                                </div>
                                                {{-- Results Dropdown --}}
                                                <div id="searchResults" class="search-results d-none"></div>
                                            </div>

                                            {{-- Map Container --}}
                                            <div id="map" class="rounded border shadow-sm" style="height: 350px;"></div>
                                            
                                            {{-- Hidden Inputs --}}
                                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                            
                                            <div class="mt-2 text-end">
                                                <small class="text-muted"><i class="mdi mdi-information-outline"></i> Click on map or drag marker to adjust.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: MANAGER & SUBMIT --}}
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-4 h-100">
                        
                        {{-- Manager Card --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="card-title text-info mb-0 fw-bold">
                                    <i class="mdi mdi-account-tie me-2"></i> Manager Login
                                </h5>
                            </div>
                            <div class="card-body p-4 bg-info bg-opacity-10">
                                <div class="alert alert-light border shadow-sm mb-4 small d-flex align-items-start">
                                    <i class="mdi mdi-shield-check text-success me-2 fs-5"></i>
                                    <div>A Store Admin account will be created automatically with these details.</div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Manager Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-account"></i></span>
                                            <input type="text" name="manager_name" class="form-control border-start-0" placeholder="Full Name" value="{{ old('manager_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Login Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-at"></i></span>
                                            <input type="email" name="manager_email" class="form-control border-start-0" placeholder="manager@example.com" value="{{ old('manager_email') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Phone (Personal)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-cellphone"></i></span>
                                            <input type="text" name="manager_phone" class="form-control border-start-0" placeholder="Mobile" value="{{ old('manager_phone') }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-lock-outline"></i></span>
                                            <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-lock-check-outline"></i></span>
                                            <input type="password" name="password_confirmation" class="form-control border-start-0" placeholder="••••••••" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="card border-0 shadow-sm mt-auto">
                            <div class="card-body p-4">
                                <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm mb-2">
                                    <i class="mdi mdi-check-circle me-2"></i> Create Store & Account
                                </button>
                                <a href="{{ route('warehouse.stores.index') }}" class="btn btn-light w-100 text-muted border">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- MAP INITIALIZATION ---
            // Default center (India) if geolocation fails or is denied
            var defaultLat = {{ old('latitude') ?? 20.5937 }};
            var defaultLng = {{ old('longitude') ?? 78.9629 }};
            var zoomLevel = {{ old('latitude') ? 16 : 5 }}; // Zoom in if old value exists

            var map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker;
            var searchInput = document.getElementById('locationSearch');

            // --- FUNCTION TO UPDATE MARKER & INPUTS ---
            function updateLocation(lat, lng, label = "") {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                    
                    // Allow dragging marker
                    marker.on('dragend', function(e) {
                         var pos = e.target.getLatLng();
                         updateInputs(pos.lat, pos.lng);
                    });
                }
                
                updateInputs(lat, lng);
                
                // Update search box label if address found
                if(label) {
                     searchInput.value = label;
                }
            }
            
            function updateInputs(lat, lng) {
                document.getElementById('latitude').value = lat.toFixed(7);
                document.getElementById('longitude').value = lng.toFixed(7);
            }

            // --- 1. CHECK OLD VALUES FIRST (Validation Errors) ---
            if ("{{ old('latitude') }}") {
                updateLocation(defaultLat, defaultLng);
                if(!marker) marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
            } 
            // --- 2. AUTO-DETECT LOCATION (If no old value) ---
            else if (navigator.geolocation) {
                searchInput.placeholder = "Detecting your location...";
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        var lat = position.coords.latitude;
                        var lng = position.coords.longitude;
                        
                        // Zoom into user location
                        map.setView([lat, lng], 16);
                        
                        // Place marker and fill inputs
                        updateLocation(lat, lng);

                        // Optional: Reverse Geocode to show address in search box
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                            .then(res => res.json())
                            .then(data => {
                                if(data && data.display_name) {
                                    searchInput.value = data.display_name;
                                } else {
                                    searchInput.placeholder = "Current Location Detected";
                                }
                            })
                            .catch(() => {
                                searchInput.placeholder = "Location Detected";
                            });
                    },
                    function(error) {
                        console.warn("Geolocation denied or failed:", error.message);
                        searchInput.placeholder = "Search manually...";
                    },
                    { enableHighAccuracy: true } // Request best possible accuracy
                );
            }

            // --- CLICK TO SET MARKER ---
            map.on('click', function(e) {
                updateLocation(e.latlng.lat, e.latlng.lng);
            });

            // --- DEBOUNCED SEARCH LOGIC ---
            const resultsBox = document.getElementById('searchResults');
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value;

                if (query.length < 3) {
                    resultsBox.classList.add('d-none');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            resultsBox.innerHTML = '';
                            if (data.length > 0) {
                                resultsBox.classList.remove('d-none');
                                data.forEach(place => {
                                    const item = document.createElement('div');
                                    item.className = 'search-result-item';
                                    item.innerHTML = `<i class="mdi mdi-map-marker me-2 text-muted"></i> ${place.display_name}`;
                                    
                                    item.onclick = function() {
                                        const lat = parseFloat(place.lat);
                                        const lon = parseFloat(place.lon);
                                        
                                        // Update Map
                                        map.setView([lat, lon], 16);
                                        updateLocation(lat, lon, place.display_name);

                                        // Close Dropdown
                                        resultsBox.classList.add('d-none');
                                    };
                                    resultsBox.appendChild(item);
                                });
                            } else {
                                resultsBox.classList.add('d-none');
                            }
                        })
                        .catch(err => console.error('Search Error:', err));
                }, 500); 
            });

            // Close search results if clicked outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    resultsBox.classList.add('d-none');
                }
            });

            // --- FORM VALIDATION ---
            (function () {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms).forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
            })()
        });
    </script>
    @endpush
</x-app-layout>