@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Customers</h1>
        <div>
            <a href="{{ route('customers.export') }}" class="btn btn-success mr-2">
                <i class="fas fa-file-excel"></i> Export CSV
            </a>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Customer
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('customers.index') }}" method="GET" class="row">
                <!-- Search -->
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search by name, email, username..."
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Service Type Filter -->
                <div class="col-md-2">
                    <select name="service_type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Service Types</option>
                        <option value="PPPoE" {{ request('service_type') == 'PPPoE' ? 'selected' : '' }}>PPPoE</option>
                        <option value="Hotspot" {{ request('service_type') == 'Hotspot' ? 'selected' : '' }}>Hotspot</option>
                        <option value="Static" {{ request('service_type') == 'Static' ? 'selected' : '' }}>Static</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="Suspended" {{ request('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <!-- View Type Toggle -->
                <div class="col-md-2">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-secondary {{ request('view') != 'map' ? 'active' : '' }}">
                            <input type="radio" name="view" value="list" {{ request('view') != 'map' ? 'checked' : '' }} onchange="this.form.submit()"> 
                            <i class="fas fa-list"></i> List
                        </label>
                        <label class="btn btn-outline-secondary {{ request('view') == 'map' ? 'active' : '' }}">
                            <input type="radio" name="view" value="map" {{ request('view') == 'map' ? 'checked' : '' }} onchange="this.form.submit()"> 
                            <i class="fas fa-map-marker-alt"></i> Map
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            @if(request('view') == 'map')
                <div id="customer-map" style="height: 600px;"></div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="customers-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Service</th>
                                <th>Contact</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $customer->id }}</td>
                                    <td>
                                        <div>{{ $customer->name }}</div>
                                        <small class="text-muted">{{ $customer->address }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $customer->username }}</div>
                                        @if($customer->active_sessions_count > 0)
                                            <small class="text-success">
                                                <i class="fas fa-circle"></i> Online
                                            </small>
                                        @else
                                            <small class="text-muted">
                                                <i class="fas fa-circle"></i> Offline
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $customer->service_type }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $customer->email }}</div>
                                        <small class="text-muted">{{ $customer->phone }}</small>
                                    </td>
                                    <td>${{ number_format($customer->balance, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($customer->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('customers.show', $customer) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" 
                                               class="btn btn-sm btn-primary" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('customers.recharge', $customer) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="Recharge">
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-customer" 
                                                    data-id="{{ $customer->id }}" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    
    <script>
        $(document).ready(function() {
            @if(request('view') != 'map')
                $('#customers-table').DataTable({
                    order: [[0, 'desc']],
                    pageLength: 25,
                    dom: 'rt<"bottom"ip>',  // Only show processing, table, info and pagination
                });
            @else
                // Initialize the map
                var map = L.map('customer-map').setView([-1.292066, 36.821945], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Add markers for customers with coordinates
                @foreach($customers as $customer)
                    @if($customer->latitude && $customer->longitude)
                        L.marker([{{ $customer->latitude }}, {{ $customer->longitude }}])
                         .bindPopup(
                            '<strong>{{ $customer->name }}</strong><br>' +
                            '{{ $customer->address }}<br>' +
                            'Service: {{ $customer->service_type }}<br>' +
                            'Status: {{ $customer->status }}<br>' +
                            '<a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-info mt-2">View Details</a>'
                         )
                         .addTo(map);
                    @endif
                @endforeach
            @endif
        });

        function confirmDelete(customerId) {
            if (confirm('Are you sure you want to delete this customer?')) {
                var form = document.getElementById('delete-form');
                form.action = '/customers/' + customerId;
                form.submit();
            }
        }
    </script>
@stop
