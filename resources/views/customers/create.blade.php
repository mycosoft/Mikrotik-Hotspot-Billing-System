@extends('adminlte::page')

@section('title', 'Add Customer')

@section('content_header')
    <h1>Add New Customer</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Information</h3>
                </div>
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-at"></i> Username
                            </label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username') }}"
                                   required>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}"
                                   required>
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="address">
                                <i class="fas fa-map-marker-alt"></i> Address
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Service Information -->
                        <h4 class="mt-4"><i class="fas fa-cogs"></i> Service Information</h4>
                        <hr>

                        <div class="form-group">
                            <label for="service_type">
                                <i class="fas fa-network-wired"></i> Service Type
                            </label>
                            <select class="form-control @error('service_type') is-invalid @enderror" 
                                    id="service_type" 
                                    name="service_type">
                                <option value="">Select Service Type</option>
                                <option value="PPPoE" {{ old('service_type') == 'PPPoE' ? 'selected' : '' }}>PPPoE</option>
                                <option value="Hotspot" {{ old('service_type') == 'Hotspot' ? 'selected' : '' }}>Hotspot</option>
                                <option value="Static" {{ old('service_type') == 'Static' ? 'selected' : '' }}>Static</option>
                            </select>
                            @error('service_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on"></i> Status
                            </label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status">
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- PPPoE Information -->
                        <div id="pppoe_fields" style="display: none;">
                            <h4 class="mt-4">PPPoE Information</h4>
                            <hr>

                            <div class="form-group">
                                <label for="pppoe_username">PPPoE Username</label>
                                <input type="text" 
                                       class="form-control @error('pppoe_username') is-invalid @enderror" 
                                       id="pppoe_username" 
                                       name="pppoe_username" 
                                       value="{{ old('pppoe_username') }}">
                                @error('pppoe_username')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="pppoe_password">PPPoE Password</label>
                                <input type="password" 
                                       class="form-control @error('pppoe_password') is-invalid @enderror" 
                                       id="pppoe_password" 
                                       name="pppoe_password">
                                @error('pppoe_password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="ip_address">IP Address</label>
                                <input type="text" 
                                       class="form-control @error('ip_address') is-invalid @enderror" 
                                       id="ip_address" 
                                       name="ip_address" 
                                       value="{{ old('ip_address') }}">
                                @error('ip_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Location Information -->
                        <h4 class="mt-4"><i class="fas fa-map"></i> Location Information (Optional)</h4>
                        <hr>

                        <div class="form-group">
                            <div id="map" style="height: 400px;"></div>
                            <small class="text-muted">Click on the map to set location or leave empty if not needed.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">
                                        <i class="fas fa-arrows-alt-v"></i> Latitude
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude') }}">
                                    @error('latitude')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">
                                        <i class="fas fa-arrows-alt-h"></i> Longitude
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude') }}">
                                    @error('longitude')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <p>Fill in the customer details:</p>
                    <ul>
                        <li>Basic information (name, contact details)</li>
                        <li>Service type and status</li>
                        <li>PPPoE credentials (if applicable)</li>
                        <li>Click on the map to set customer location</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map centered on Kampala, Uganda
            var map = L.map('map').setView([0.3476, 32.5825], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: ' OpenStreetMap contributors'
            }).addTo(map);

            var marker;

            // Handle map clicks
            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                // Update form fields
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);

                // Update or create marker
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });

            // Handle service type changes
            document.getElementById('service_type').addEventListener('change', function() {
                var pppoeFields = document.getElementById('pppoe_fields');
                if (this.value === 'PPPoE') {
                    pppoeFields.style.display = 'block';
                } else {
                    pppoeFields.style.display = 'none';
                }
            });

            // Trigger change event to set initial state
            document.getElementById('service_type').dispatchEvent(new Event('change'));
        });
    </script>
@stop
