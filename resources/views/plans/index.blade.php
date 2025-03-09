@extends('adminlte::page')

@section('title', 'Internet Plans')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Internet Plans</h1>
        <div>
            <a href="{{ route('plans.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Create Plan
            </a>
            <button onclick="syncAllPlans()" class="btn btn-primary">
                <i class="fas fa-sync"></i> Sync All Plans
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filters Card --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('plans.index') }}" method="GET" class="row">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="limited" {{ request('type') === 'limited' ? 'selected' : '' }}>Limited</option>
                                <option value="unlimited" {{ request('type') === 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="bandwidth_id" class="form-control">
                                <option value="">All Bandwidths</option>
                                @foreach($bandwidths as $bandwidth)
                                    <option value="{{ $bandwidth->id }}" {{ request('bandwidth_id') == $bandwidth->id ? 'selected' : '' }}>
                                        {{ $bandwidth->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="router_id" class="form-control">
                                <option value="">All Routers</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('plans.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Plans Table --}}
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Bandwidth</th>
                                <th>Price</th>
                                <th>Validity</th>
                                <th>Router</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td>{{ $plan->name }}</td>
                                    <td>
                                        {{ ucfirst($plan->type) }}
                                        @if($plan->type === 'limited')
                                            <br>
                                            <small class="text-muted">
                                                @if(in_array($plan->limit_type, ['time', 'both']))
                                                    {{ $plan->formatted_time_limit }}
                                                @endif
                                                @if($plan->limit_type === 'both')
                                                    /
                                                @endif
                                                @if(in_array($plan->limit_type, ['data', 'both']))
                                                    {{ $plan->formatted_data_limit }}
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan->bandwidth)
                                            <span class="badge badge-{{ $plan->bandwidth->is_active ? 'success' : 'danger' }}">
                                                {{ $plan->bandwidth->name }}
                                            </span>
                                        @else
                                            <span class="badge badge-warning">No Bandwidth</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($plan->price) }}</td>
                                    <td>{{ $plan->validity_days }} days</td>
                                    <td>
                                        @if($plan->router)
                                            {{ $plan->router->name }}
                                            <br>
                                            <small class="text-{{ $plan->router->is_active ? 'success' : 'danger' }}">
                                                {{ $plan->router->is_active ? 'Active' : 'Inactive' }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button onclick="syncPlan({{ $plan->id }})" 
                                                    class="btn btn-sm btn-info" 
                                                    {{ !$plan->router || !$plan->router->is_active ? 'disabled' : '' }}
                                                    title="Sync">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="window.location='{{ route('plans.edit', $plan) }}'"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deletePlan('{{ $plan->name }}', {{ $plan->id }})" 
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No plans found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $plans->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function syncAllPlans() {
    const button = document.querySelector('button[onclick="syncAllPlans()"]');
    const icon = button.querySelector('i');
    
    // Show loading state
    button.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';

    $.post("{{ route('plans.sync') }}", {
        _token: '{{ csrf_token() }}'
    }).done(function(response) {
        Swal.fire({
            title: response.title,
            text: response.message,
            icon: response.icon,
            confirmButtonColor: '#3085d6'
        }).then(() => window.location.reload());
    }).fail(function(xhr) {
        const response = xhr.responseJSON || {};
        Swal.fire({
            title: response.title || 'Error!',
            text: response.message || 'Failed to sync plans',
            icon: response.icon || 'error',
            confirmButtonColor: '#3085d6'
        });
    }).always(function() {
        button.disabled = false;
        icon.className = 'fas fa-sync';
    });
}

function syncPlan(planId) {
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    
    // Show loading state
    button.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';

    $.post("{{ route('plans.sync') }}", {
        _token: '{{ csrf_token() }}',
        plan_id: planId
    }).done(function(response) {
        Swal.fire({
            title: response.title,
            text: response.message,
            icon: response.icon,
            confirmButtonColor: '#3085d6'
        });
    }).fail(function(xhr) {
        const response = xhr.responseJSON || {};
        Swal.fire({
            title: response.title || 'Error!',
            text: response.message || 'Failed to sync plan',
            icon: response.icon || 'error',
            confirmButtonColor: '#3085d6'
        });
    }).always(function() {
        button.disabled = false;
        icon.className = 'fas fa-sync';
    });
}

function deletePlan(name, planId) {
    Swal.fire({
        title: 'Delete Plan',
        text: `Are you sure you want to delete "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('plans.destroy', '') }}/" + planId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire('Deleted!', response.message, 'success')
                        .then(() => window.location.reload());
                },
                error: function(xhr) {
                    const error = xhr.responseJSON || {};
                    Swal.fire('Error', error.message || 'Failed to delete plan', 'error');
                }
            });
        }
    });
}
</script>
@endpush

@push('css')
<style>
.btn-group .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-group {
    display: flex;
    gap: 0.5rem;
}
</style>
@endpush
