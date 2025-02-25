@extends('adminlte::page')

@section('title', 'Router Maps')

@section('content_header')
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Router Geo Location</h1>
                <div class="btn-group">
                    <a href="{{ route('routers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Router List
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Router Locations</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('routers.maps') }}" class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="name" class="form-control float-right" placeholder="Search Router" 
                                value="{{ request('name') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div id="router-map" style="height: 600px;"></div>
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
        $(document).ready(function() {
            var routerMap = L.map('router-map').setView([0.3476, 32.5825], 6);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(routerMap);

            var routers = @json($routers);

            routers.forEach(function(router) {
                if (router.coordinates) {
                    var coords = router.coordinates.split(',').map(parseFloat);
                    
                    // Create marker
                    var marker = L.marker(coords).addTo(routerMap);
                    
                    // Create popup content
                    var popupContent = `
                        <b>${router.name}</b><br>
                        IP: ${router.ip_address}<br>
                        Status: ${router.is_active ? 'Enabled' : 'Disabled'}<br>
                        <a href="{{ route('routers.edit', '') }}/${router.id}">Edit Router</a>
                    `;
                    
                    // Add popup to marker
                    marker.bindPopup(popupContent);

                    // Add circle for coverage if available
                    if (router.coverage) {
                        L.circle(coords, {
                            color: router.is_active ? 'green' : 'red',
                            fillColor: router.is_active ? '#2ecc71' : '#e74c3c',
                            fillOpacity: 0.1,
                            radius: router.coverage || 5
                        }).addTo(routerMap);
                    }
                }
            });
        });
    </script>
@stop
