@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Dashboard</h1>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <style>
        .info-box {
            border-radius: 4px;
            position: relative;
            display: block;
            min-height: 125px;
            background: #fff;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .info-box.bg-primary { background-color: #4e73df !important; }
        .info-box.bg-success { background-color: #1cc88a !important; }
        .info-box.bg-warning { background-color: #f6c23e !important; }
        .info-box.bg-danger { background-color: #e74a3b !important; }
        
        .info-box-content {
            padding: 5px 10px;
            margin-left: 0;
            text-align: center;
            color: #fff;
        }
        
        .info-box-text {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
            margin-top: 10px;
        }
        
        .info-box-number {
            display: block;
            font-weight: bold;
            font-size: 24px;
            margin-top: 5px;
        }
        
        .more-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 3px 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            display: block;
            z-index: 10;
            background: rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        
        .more-info:hover {
            color: #fff;
            background: rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }
    </style>

    <!-- Info Boxes -->
    <div class="row justify-content-center">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-primary">
                <div class="info-box-content">
                    <span class="info-box-text">Income Today</span>
                    <span class="info-box-number">{{ $currency }} {{ number_format($todayIncome) }}</span>
                </div>
                <a href="{{ route('reports.daily') }}" class="more-info">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-success">
                <div class="info-box-content">
                    <span class="info-box-text">Monthly Income</span>
                    <span class="info-box-number">{{ $currency }} {{ number_format($monthlyIncome) }}</span>
                </div>
                <a href="{{ route('reports.monthly') }}" class="more-info">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-warning">
                <div class="info-box-content">
                    <span class="info-box-text">Active/Expired</span>
                    <span class="info-box-number">{{ $activeCustomers }}/{{ $expiredCustomers }}</span>
                </div>
                <a href="{{ route('customers.index') }}" class="more-info">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-danger">
                <div class="info-box-content">
                    <span class="info-box-text">Customers</span>
                    <span class="info-box-number">{{ $totalCustomers }}</span>
                </div>
                <a href="{{ route('customers.index') }}" class="more-info">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row">
        @if(!$hideMonthlyRegistered)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Monthly Registered Customers
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="registeredChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        @endif

        @if(!$hideMonthlySales)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            Monthly Sales
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Customer Stats and Voucher Stats Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Customer Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="customerChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        @if(!$hideVoucherStock)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Voucher Stock</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Package Name</th>
                                    <th>Unused</th>
                                    <th>Used</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($voucherStats as $stat)
                                    <tr>
                                        <td>{{ $stat['name_plan'] }}</td>
                                        <td>{{ $stat['unused'] }}</td>
                                        <td>{{ $stat['used'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="font-weight-bold">
                                    <td>Total</td>
                                    <td>{{ $totalVoucherStats['unused'] }}</td>
                                    <td>{{ $totalVoucherStats['used'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Users Expiring and Offline Routers -->
    <div class="row">
        @if(!$hideUserExpired)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger">
                        <h3 class="card-title text-white">Users Expiring Today</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Plan</th>
                                    <th>Router</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringToday as $customer)
                                    <tr>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->plan->name ?? 'N/A' }}</td>
                                        <td>{{ $customer->router->name ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No users expiring today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white">Offline Routers</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Router</th>
                                <th>IP Address</th>
                                <th>Last Seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offlineRouters as $router)
                                <tr>
                                    <td>{{ $router->name }}</td>
                                    <td>{{ $router->ip_address }}</td>
                                    <td>{{ $router->last_seen ? $router->last_seen->diffForHumans() : 'Never' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No offline routers</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Registered Customers Chart
    @if(!$hideMonthlyRegistered)
        const registeredCtx = document.getElementById('registeredChart').getContext('2d');
        new Chart(registeredCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($monthlyRegistered)) !!},
                datasets: [{
                    label: 'Registered Customers',
                    data: {!! json_encode(array_values($monthlyRegistered)) !!},
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    @endif

    // Monthly Sales Chart
    @if(!$hideMonthlySales)
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($monthlySales)) !!},
                datasets: [{
                    label: 'Total Sales',
                    data: {!! json_encode(array_values($monthlySales)) !!},
                    backgroundColor: 'rgba(40,167,69,0.9)',
                    borderColor: 'rgba(40,167,69,0.8)',
                    pointRadius: true,
                    pointColor: '#28a745',
                    pointStrokeColor: 'rgba(40,167,69,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(40,167,69,1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    @endif

    // Customer Statistics Pie Chart
    const customerCtx = document.getElementById('customerChart').getContext('2d');
    new Chart(customerCtx, {
        type: 'pie',
        data: {
            labels: ['Active', 'Expired', 'Inactive'],
            datasets: [{
                data: [
                    {{ $activeCustomers }}, 
                    {{ $expiredCustomers }}, 
                    {{ \App\Models\Customer::where('status', 'Inactive')->count() }}
                ],
                backgroundColor: ['#28a745', '#dc3545', '#6c757d'],
                borderColor: ['#28a745', '#dc3545', '#6c757d'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@stop
