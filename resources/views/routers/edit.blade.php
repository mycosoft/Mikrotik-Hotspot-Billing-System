@extends('adminlte::page')

@section('title', 'Edit Router')

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Router: {{ $router->name }}</h3>
            </div>
            <form class="form-horizontal" method="post" action="{{ route('routers.update', $router) }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Status</label>
                        <div class="col-md-10">
                            <label class="radio-inline">
                                <input type="radio" name="is_active" value="1" {{ $router->is_active ? 'checked' : '' }}> Enable
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="is_active" value="0" {{ !$router->is_active ? 'checked' : '' }}> Disable
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Router Name / Location</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="name" name="name" maxlength="32" value="{{ $router->name }}">
                            <p class="help-block text-muted">Name of Area that router operated</p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">IP Address</label>
                        <div class="col-md-6">
                            <input type="text" placeholder="192.168.88.1:8728" class="form-control" id="ip" name="ip" value="{{ $router->ip }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Username</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="username" name="username" value="{{ $router->username }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Router Secret</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="password" name="password"
                                   onmouseleave="this.type = 'password'" onmouseenter="this.type = 'text'"
                                   placeholder="Leave empty to keep current password">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Description</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="description" name="description">{{ $router->description }}</textarea>
                            <p class="help-block text-muted">Explain Coverage of router</p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label"></label>
                        <div class="col-md-6">
                            <label>
                                <input type="checkbox" checked name="test_connection" value="1"> Test Connection
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="col-lg-offset-2 col-lg-10">
                        <button class="btn btn-primary" type="submit">Save Changes</button>
                        Or <a href="{{ route('routers.index') }}">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var map, circle, marker;

        function togglePasswordVisibility(button) {
            var passwordInput = button.closest('.input-group').find('input');
            var type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
            passwordInput.attr('type', type);
            button.find('i').toggleClass('fa-eye fa-eye-slash');
        }

        function getLocation() {
            // Default to Uganda's central location (Kampala)
            setupMap(0.3476, 32.5825);
        }

        function showPosition(position) {
            setupMap(position.coords.latitude, position.coords.longitude);
        }

        function updateCoverage() {
            if (circle !== undefined) {
                circle.setRadius($("#coverage").val());
            }
        }

        function setupMap(lat, lon) {
            if (map) {
                map.remove();
            }

            map = L.map('map').setView([lat, lon], 13);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            marker = L.marker([lat, lon]).addTo(map);
            circle = L.circle([lat, lon], {
                color: 'blue',
                fillOpacity: 0.1,
                radius: $("#coverage").val() || 5
            }).addTo(map);

            map.on('click', function(e) {
                var coord = e.latlng;
                var lat = coord.lat;
                var lng = coord.lng;
                
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                $('#coordinates').val(lat + ',' + lng);
                updateCoverage();
            });
        }

        function testConnection() {
            const button = event.target;
            const icon = button.querySelector('i');
            
            // Disable button and show loading state
            button.disabled = true;
            icon.className = 'fas fa-spinner fa-spin';
            
            $.ajax({
                url: '{{ route('routers.test-connection', $router->id) }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to test connection',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Re-enable button and restore icon
                    button.disabled = false;
                    icon.className = 'fas fa-sync';
                }
            });
        }

        $(document).ready(function() {
            @if($router->coordinates)
                setupMap({{ $router->coordinates }});
            @else
                getLocation();
            @endif
        });
    </script>
@stop
