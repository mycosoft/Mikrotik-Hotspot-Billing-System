@extends('adminlte::page')

@section('title', 'Add Router')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Add Router</h1>
        <a href="{{ route('routers.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add Router</h3>
            </div>
            <form class="form-horizontal" method="post" action="{{ route('routers.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Status</label>
                        <div class="col-md-10">
                            <label class="radio-inline">
                                <input type="radio" checked name="is_active" value="1"> Enable
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="is_active" value="0"> Disable
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Router Name / Location</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="name" name="name" maxlength="32">
                            <p class="help-block text-muted">Name of Area that router operated</p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">IP Address</label>
                        <div class="col-md-6">
                            <input type="text" placeholder="192.168.88.1:8728" class="form-control" id="ip" name="ip">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Username</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="username" name="username">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Router Secret</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="password" name="password"
                                   onmouseleave="this.type = 'password'" onmouseenter="this.type = 'text'">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Description</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="description" name="description"></textarea>
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
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        var map, circle, marker;

        function togglePasswordVisibility(button) {
            var passwordInput = button.closest('.input-group').querySelector('input');
            var type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            button.querySelector('i').classList.toggle('fa-eye');
            button.querySelector('i').classList.toggle('fa-eye-slash');
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
            // Disable test connection for create form as no router exists yet
            Swal.fire({
                icon: 'info',
                title: 'Test Connection',
                text: 'Please create the router first, then test connection from the edit page.'
            });
        }

        $(document).ready(function() {
            getLocation();
        });
    </script>
@stop
