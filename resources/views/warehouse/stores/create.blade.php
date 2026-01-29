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
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
        }
        .search-result-item:hover {
            background-color: #f1f3f5;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
    </style>
    @endpush

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-success text-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold"><i class="mdi mdi-plus-box me-2"></i> Register New Store</h4>
            <a href="{{ route('warehouse.stores.index') }}" class="btn btn-light btn-sm text-success fw-bold">
                <i class="mdi mdi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('warehouse.stores.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            
            <div class="row">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-primary mb-0"><i class="mdi mdi-store me-2"></i> Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="store_name" class="form-control form-control-lg" placeholder="e.g. Southwest Farmers Lucknow" value="{{ old('store_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Store Email <span class="text-danger">*</span></label>
                                    <input type="email" name="store_email" class="form-control" placeholder="store@example.com" value="{{ old('store_email') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="store_phone" class="form-control" placeholder="+91 98765..." value="{{ old('store_phone') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Full Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Building No, Street..." required>{{ old('address') }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" placeholder="City" value="{{ old('city') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control" placeholder="State" value="{{ old('state') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" placeholder="123456" value="{{ old('pincode') }}" required>
                                </div>
                                
                                <div class="col-md-12 mt-4">
                                    <label class="form-label fw-bold mb-1"><i class="mdi mdi-map-marker-radius me-1 text-danger"></i> Pin Location on Map</label>
                                    
                                    {{-- Search Box --}}
                                    <div class="position-relative mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="mdi mdi-magnify"></i></span>
                                            <input type="text" id="locationSearch" class="form-control" placeholder="Fetching your location..." autocomplete="off">
                                        </div>
                                        {{-- Results Dropdown --}}
                                        <div id="searchResults" class="search-results d-none"></div>
                                    </div>

                                    {{-- Map Container --}}
                                    <div id="map" style="height: 350px; border-radius: 8px; border: 1px solid #ddd; z-index: 1;"></div>
                                    
                                    {{-- Hidden Inputs --}}
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                    
                                    <small class="text-muted mt-1 d-block"><i class="mdi mdi-crosshairs-gps me-1 text-primary"></i> Automatically detecting your location...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-info mb-0"><i class="mdi mdi-account-tie me-2"></i> Manager Login</h5>
                        </div>
                        <div class="card-body bg-light bg-opacity-25">
                            <div class="alert alert-info border-0 shadow-sm mb-3">
                                <i class="mdi mdi-information-outline me-1"></i> A Super Admin account will be created automatically.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Manager Name <span class="text-danger">*</span></label>
                                    <input type="text" name="manager_name" class="form-control" placeholder="Full Name" value="{{ old('manager_name') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Login Email <span class="text-danger">*</span></label>
                                    <input type="email" name="manager_email" class="form-control" placeholder="manager@example.com" value="{{ old('manager_email') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Phone (Personal)</label>
                                    <input type="text" name="manager_phone" class="form-control" placeholder="Mobile" value="{{ old('manager_phone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg shadow">
                            <i class="mdi mdi-check-circle me-2"></i> Create Store & Account
                        </button>
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
                    marker = L.marker([lat, lng]).addTo(map);
                }
                
                // Update hidden inputs
                document.getElementById('latitude').value = lat.toFixed(7);
                document.getElementById('longitude').value = lng.toFixed(7);
                
                // Update search box label if address found
                if(label) {
                     searchInput.value = label;
                }
            }

            // --- 1. CHECK OLD VALUES FIRST (Validation Errors) ---
            if ("{{ old('latitude') }}") {
                updateLocation(defaultLat, defaultLng);
            } 
            // --- 2. AUTO-DETECT LOCATION (If no old value) ---
            else if (navigator.geolocation) {
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
                                    searchInput.value = "Current Location Detected";
                                }
                            })
                            .catch(() => {
                                searchInput.value = "Location Detected";
                            });
                    },
                    function(error) {
                        console.warn("Geolocation denied or failed:", error.message);
                        searchInput.placeholder = "Location detection failed. Search manually...";
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
                                    item.innerText = place.display_name;
                                    
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