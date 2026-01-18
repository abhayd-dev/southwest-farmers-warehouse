<x-app-layout title="Edit Store">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
                                    <label class="form-label fw-bold"><i class="mdi mdi-map-marker-radius me-1"></i> Update Location</label>
                                    <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;"></div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <input type="text" name="latitude" id="latitude" class="form-control form-control-sm bg-light" value="{{ $store->latitude }}" placeholder="Latitude" readonly>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="longitude" id="longitude" class="form-control form-control-sm bg-light" value="{{ $store->longitude }}" placeholder="Longitude" readonly>
                                        </div>
                                    </div>
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
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">
                            <i class="mdi mdi-content-save me-2"></i> Update Store Details
                        </button>
                        <a href="{{ route('warehouse.stores.show', $store->id) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Load existing lat/lng or default
            var lat = {{ $store->latitude ?? 20.5937 }};
            var lng = {{ $store->longitude ?? 78.9629 }};
            
            var map = L.map('map').setView([lat, lng], {{ $store->latitude ? 13 : 5 }});

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var marker;
            if ({{ $store->latitude ? 'true' : 'false' }}) {
                marker = L.marker([lat, lng]).addTo(map);
            }

            map.on('click', function(e) {
                var newLat = e.latlng.lat.toFixed(6);
                var newLng = e.latlng.lng.toFixed(6);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }

                document.getElementById('latitude').value = newLat;
                document.getElementById('longitude').value = newLng;
            });
        });
    </script>
    @endpush
</x-app-layout>