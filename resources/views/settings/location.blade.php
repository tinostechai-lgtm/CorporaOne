@extends('layouts.admin')

@section('page-title', 'Location Settings')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        {{ __('Company Location Settings') }}
                    </h4>
                    <small>{{ __('Configure office location for attendance marking') }}</small>
                </div>

                <div class="card-body">
                    {{Form::open(array('route'=>'settings.location.update','method'=>'POST', 'id'=>'locationForm'))}}
                    @csrf

                    <div class="row">
                        <!-- Enable Location Restriction -->
                        <div class="col-12 mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="location_restriction" 
                                       name="location_restriction" value="1" 
                                       {{ (isset($settings['location_restriction']) && $settings['location_restriction'] == 'on') ? 'checked' : '' }}>
                                <label class="form-check-label" for="location_restriction">
                                    <strong>{{ __('Enable Location Restriction') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ __('Employees must be within the office radius to mark attendance') }}</small>
                                </label>
                            </div>
                        </div>

                        <!-- Office Address -->
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                {{Form::label('office_address', __('Office Address'), ['class'=>'form-label'])}}
                                {{Form::text('office_address', $settings['office_address'] ?? '', ['class'=>'form-control', 'placeholder'=>__('Enter office address'), 'id'=>'office_address'])}}
                                <small class="text-muted">{{ __('Enter the full address of your office') }}</small>
                            </div>
                        </div>

                        <!-- Get Current Location -->
                        <div class="col-12 mb-3">
                            <button type="button" class="btn btn-info" id="getCurrentLocation">
                                <i class="fas fa-crosshairs me-2"></i> {{ __('Get Current Location') }}
                            </button>
                            <button type="button" class="btn btn-secondary" id="searchLocation">
                                <i class="fas fa-search me-2"></i> {{ __('Search Address') }}
                            </button>
                            <small class="d-block text-muted mt-1">{{ __('Click to get your current location or search for an address') }}</small>
                        </div>

                        <!-- Coordinates -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                {{Form::label('office_latitude', __('Latitude'), ['class'=>'form-label'])}}<x-required></x-required>
                                {{Form::text('office_latitude', $settings['office_latitude'] ?? '', ['class'=>'form-control', 'placeholder'=>__('e.g. 28.6139'), 'required'=>'required', 'id'=>'office_latitude'])}}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                {{Form::label('office_longitude', __('Longitude'), ['class'=>'form-label'])}}<x-required></x-required>
                                {{Form::text('office_longitude', $settings['office_longitude'] ?? '', ['class'=>'form-control', 'placeholder'=>__('e.g. 77.2090'), 'required'=>'required', 'id'=>'office_longitude'])}}
                            </div>
                        </div>

                        <!-- Radius -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                {{Form::label('office_radius', __('Radius (meters)'), ['class'=>'form-label'])}}
                                {{Form::number('office_radius', $settings['office_radius'] ?? 300, ['class'=>'form-control', 'min'=>'50', 'max'=>'5000', 'id'=>'office_radius'])}}
                                <small class="text-muted">{{ __('Default: 300 meters. Min: 50, Max: 5000') }}</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Radius Preview') }}</label>
                                <div class="form-control bg-light" style="height: 40px; display: flex; align-items: center;">
                                    <span id="radiusDisplay">{{ $settings['office_radius'] ?? 300 }} meters</span>
                                </div>
                            </div>
                        </div>

                        <!-- Map Preview -->
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">{{ __('Location Preview') }}</label>
                                <div id="map" style="height: 400px; width: 100%; border-radius: 8px; background: #f0f0f0;"></div>
                                <small class="text-muted">{{ __('The blue marker shows your office location. The circle shows the allowed radius.') }}</small>
                            </div>
                        </div>

                        <!-- Quick Tips -->
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ __('How it works:') }}</strong>
                                <ul class="mb-0 mt-1">
                                    <li>{{ __('When employees mark attendance, they will be asked for their location') }}</li>
                                    <li>{{ __('If location restriction is enabled, they must be within the office radius') }}</li>
                                    <li>{{ __('Employees can choose "Office" or "Remote" when marking attendance') }}</li>
                                    <li>{{ __('Office mode will check GPS location against the office coordinates') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer px-0 mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('Save Location Settings') }}
                        </button>
                    </div>

                    {{Form::close()}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet.js for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let map = null;
        let marker = null;
        let circle = null;
        let currentLocation = null;

        // Initialize map
        function initMap(lat, lng) {
            if (map) {
                map.remove();
            }

            map = L.map('map', {
                center: [lat || 28.6139, lng || 77.2090],
                zoom: 15,
                zoomControl: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add marker
            const icon = L.icon({
                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41]
            });

            marker = L.marker([lat || 28.6139, lng || 77.2090], { icon: icon, draggable: true })
                .addTo(map)
                .bindPopup('Office Location');

            // Add circle for radius
            const radius = parseInt(document.getElementById('office_radius').value) || 300;
            circle = L.circle([lat || 28.6139, lng || 77.2090], {
                radius: radius,
                color: '#007bff',
                fillColor: '#007bff',
                fillOpacity: 0.1
            }).addTo(map);

            // Marker drag events
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                document.getElementById('office_latitude').value = pos.lat.toFixed(6);
                document.getElementById('office_longitude').value = pos.lng.toFixed(6);
                updateCircle(pos.lat, pos.lng);
                updateAddress(pos.lat, pos.lng);
            });

            // Make marker draggable
            marker.dragging.enable();

            // Fit map to show everything
            setTimeout(() => {
                if (map) {
                    map.fitBounds(circle.getBounds());
                }
            }, 100);

            // Update radius on change
            document.getElementById('office_radius').addEventListener('input', function() {
                const radius = parseInt(this.value) || 300;
                document.getElementById('radiusDisplay').textContent = radius + ' meters';
                if (circle) {
                    circle.setRadius(radius);
                }
            });

            // Update coordinates when inputs change
            document.getElementById('office_latitude').addEventListener('change', updateMapFromInputs);
            document.getElementById('office_longitude').addEventListener('change', updateMapFromInputs);
        }

        function updateCircle(lat, lng) {
            if (circle) {
                circle.setLatLng([lat, lng]);
            }
            if (marker) {
                marker.setLatLng([lat, lng]);
            }
            if (map) {
                map.panTo([lat, lng]);
            }
        }

        function updateMapFromInputs() {
            const lat = parseFloat(document.getElementById('office_latitude').value);
            const lng = parseFloat(document.getElementById('office_longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) {
                updateCircle(lat, lng);
                updateAddress(lat, lng);
            }
        }

        function updateAddress(lat, lng) {
            // Reverse geocoding
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('office_address').value = data.display_name;
                    }
                })
                .catch(() => {
                    // Silent fail - don't update address if reverse geocoding fails
                });
        }

        // Get current location
        document.getElementById('getCurrentLocation').addEventListener('click', function() {
            if (navigator.geolocation) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Getting location...';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        document.getElementById('office_latitude').value = lat.toFixed(6);
                        document.getElementById('office_longitude').value = lng.toFixed(6);
                        updateCircle(lat, lng);
                        updateAddress(lat, lng);
                        document.getElementById('getCurrentLocation').disabled = false;
                        document.getElementById('getCurrentLocation').innerHTML = '<i class="fas fa-crosshairs me-2"></i> Get Current Location';
                        showToast('Location updated successfully!', 'success');
                    },
                    function(error) {
                        document.getElementById('getCurrentLocation').disabled = false;
                        document.getElementById('getCurrentLocation').innerHTML = '<i class="fas fa-crosshairs me-2"></i> Get Current Location';
                        showToast('Error getting location: ' + error.message, 'error');
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                showToast('Geolocation is not supported by your browser.', 'error');
            }
        });

        // Search location
        document.getElementById('searchLocation').addEventListener('click', function() {
            const address = document.getElementById('office_address').value;
            if (!address) {
                showToast('Please enter an address first.', 'warning');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Searching...';

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        document.getElementById('office_latitude').value = lat.toFixed(6);
                        document.getElementById('office_longitude').value = lng.toFixed(6);
                        updateCircle(lat, lng);
                        showToast('Location found!', 'success');
                    } else {
                        showToast('Address not found. Please try again.', 'error');
                    }
                    document.getElementById('searchLocation').disabled = false;
                    document.getElementById('searchLocation').innerHTML = '<i class="fas fa-search me-2"></i> Search Address';
                })
                .catch(() => {
                    document.getElementById('searchLocation').disabled = false;
                    document.getElementById('searchLocation').innerHTML = '<i class="fas fa-search me-2"></i> Search Address';
                    showToast('Error searching address.', 'error');
                });
        });

        // Toast notification
        function showToast(message, type = 'success') {
            let container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'toast-container';
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;max-width:400px;';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toast.style.cssText = `
                padding: 12px 16px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                margin-bottom: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                animation: slideInRight 0.5s ease;
                display: flex;
                align-items: center;
                gap: 10px;
                background: ${colors[type] || colors.info};
                min-width: 200px;
            `;
            toast.innerHTML = `
                <span><i class="fas ${icons[type] || icons.info}"></i></span>
                <span>${message}</span>
                <button class="btn btn-sm btn-link text-white" onclick="this.parentElement.remove()" style="margin-left:auto;text-decoration:none;">&times;</button>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'slideOutRight 0.5s ease forwards';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 4000);
        }

        // Initialize map
        const initLat = parseFloat(document.getElementById('office_latitude').value) || 28.6139;
        const initLng = parseFloat(document.getElementById('office_longitude').value) || 77.2090;
        initMap(initLat, initLng);

        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection