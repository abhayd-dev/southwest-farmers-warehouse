<x-app-layout title="Edit Store">
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
    </style>
    @endpush

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-info text-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold"><i class="mdi mdi-pencil-box me-2"></i> Edit Store Details</h4>
            <a href="{{ route('warehouse.stores.index') }}" class="btn btn-light btn-sm text-info fw-bold">
                <i class="mdi mdi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('warehouse.stores.update', $store->id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-primary mb-0"><i class="mdi mdi-store me-2"></i> Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="store_name" class="form-control form-control-lg" value="{{ old('store_name', $store->store_name) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Store Email <span class="text-danger">*</span></label>
                                    <input type="email" name="store_email" class="form-control" value="{{ old('store_email', $store->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="store_phone" class="form-control" value="{{ old('store_phone', $store->phone) }}" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Full Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" required>{{ old('address', $store->address) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $store->city) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control" value="{{ old('state', $store->state) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $store->pincode) }}" required>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <label class="form-label fw-bold mb-1"><i class="mdi mdi-map-marker-radius me-1 text-danger"></i> Update Location</label>
                                    
                                    {{-- Search Box --}}
                                    <div class="position-relative mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="mdi mdi-magnify"></i></span>
                                            <input type="text" id="locationSearch" class="form-control" placeholder="Search new location..." autocomplete="off">
                                        </div>
                                        <div id="searchResults" class="search-results d-none"></div>
                                    </div>

                                    {{-- Map --}}
                                    <div id="map" style="height: 350px; border-radius: 8px; border: 1px solid #ddd;"></div>
                                    
                                    {{-- Hidden Inputs --}}
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $store->latitude) }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $store->longitude) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-dark mb-0"><i class="mdi mdi-toggle-switch me-2"></i> Store Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="isActiveSwitch" name="is_active" value="1" {{ $store->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="isActiveSwitch">Store Active</label>
                            </div>
                            <small class="text-muted d-block mt-2">Disabling this prevents manager login.</small>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4 bg-light">
                        <div class="card-header bg-transparent border-bottom-0 py-3">
                            <h5 class="card-title text-info mb-0"><i class="mdi mdi-account-tie me-2"></i> Assigned Manager</h5>
                        </div>
                        <div class="card-body pt-0">
                            @if($store->manager)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm bg-white text-info rounded-circle d-flex justify-content-center align-items-center shadow-sm me-3">
                                        <i class="mdi mdi-account fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $store->manager->name }}</h6>
                                        <small class="text-muted">{{ $store->manager->email }}</small>
                                    </div>
                                </div>
                                <div class="alert alert-warning border-0 shadow-sm py-2 px-3 small">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> To change, visit <a href="{{ route('warehouse.staff.index') }}" class="alert-link">Store Panel -> My Profile Update</a>.
                                </div>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="mdi mdi-account-off fs-1 opacity-25"></i>
                                    <p class="mb-0">No Manager Assigned</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">
                            <i class="mdi mdi-content-save me-2"></i> Update Store
                        </button>
                        <a href="{{ route('warehouse.stores.show', $store->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Existing Coordinates or Default (India)
            var lat = {{ $store->latitude ?? 20.5937 }};
            var lng = {{ $store->longitude ?? 78.9629 }};
            var zoom = {{ $store->latitude ? 14 : 5 }};
            
            var map = L.map('map').setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker;
            // Place marker if coordinates exist
            if ({{ $store->latitude ? 'true' : 'false' }}) {
                marker = L.marker([lat, lng]).addTo(map);
            }

            // Click Handler
            map.on('click', function(e) {
                updateLocation(e.latlng.lat, e.latlng.lng);
            });

            function updateLocation(lat, lng) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(map);
                }
                document.getElementById('latitude').value = lat.toFixed(7);
                document.getElementById('longitude').value = lng.toFixed(7);
            }

            // Debounced Search
            const searchInput = document.getElementById('locationSearch');
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
                                        
                                        map.setView([lat, lon], 16);
                                        updateLocation(lat, lon);
                                        
                                        searchInput.value = place.display_name;
                                        resultsBox.classList.add('d-none');
                                    };
                                    resultsBox.appendChild(item);
                                });
                            } else {
                                resultsBox.classList.add('d-none');
                            }
                        });
                }, 500);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    resultsBox.classList.add('d-none');
                }
            });

            // Validation
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