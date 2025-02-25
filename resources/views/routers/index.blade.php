@extends('adminlte::page')

@section('title', 'Routers')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Routers</h1>
        <a href="{{ route('routers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Router
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('routers.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name" 
                                   value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('routers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Router Name</th>
                        <th>IP Address</th>
                        <th>Username</th>
                        <th>Description</th>
                        <th>Online Status</th>
                        <th>Last Seen</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routers as $router)
                        <tr>
                            <td>{{ $router->name }}</td>
                            <td class="text-monospace">{{ $router->ip }}</td>
                            <td class="text-monospace">{{ $router->username }}</td>
                            <td>{{ $router->description }}</td>
                            <td>
                                <span class="badge badge-{{ $router->is_online ? 'success' : 'danger' }} router-status" 
                                      data-router-id="{{ $router->id }}">
                                    {{ $router->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td>
                                <span class="router-last-seen" data-router-id="{{ $router->id }}">
                                    {{ $router->last_seen ? $router->last_seen->diffForHumans() : 'Never' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $router->is_active ? 'success' : 'danger' }}">
                                    {{ $router->is_active ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('routers.edit', $router) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $router->name }}', {{ $router->id }})"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-info sync-btn" 
                                            onclick="syncRouter(this, {{ $router->id }}, '{{ $router->name }}')"
                                            title="Sync Router">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No routers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($routers->hasPages())
            <div class="card-footer">
                {{ $routers->appends(request()->input())->links() }}
            </div>
        @endif
    </div>

    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
    function confirmDelete(routerName, routerId) {
        Swal.fire({
            title: 'Delete Router?',
            text: `Are you sure you want to delete router "${routerName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = `/routers/${routerId}`;
                form.submit();
            }
        });
    }

    function syncRouter(button, routerId, routerName) {
        // Get the icon element
        const icon = button.querySelector('i');
        
        // Disable button and show spinner
        button.disabled = true;
        icon.classList.remove('fa-sync');
        icon.classList.add('fa-spinner', 'fa-spin');
        
        // Make the sync request
        fetch(`/routers/${routerId}/sync`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: `Router "${routerName}" synced successfully!`
                });
                
                // Update the router status
                updateRouterStatus(routerId);
            } else {
                throw new Error(data.message || 'Failed to sync router.');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Something went wrong while syncing the router.'
            });
        })
        .finally(() => {
            // Re-enable button and restore icon
            button.disabled = false;
            icon.classList.remove('fa-spinner', 'fa-spin');
            icon.classList.add('fa-sync');
        });
    }

    function updateRouterStatus(routerId) {
        fetch(`/routers/${routerId}/status`)
            .then(response => response.json())
            .then(data => {
                const statusBadge = document.querySelector(`.router-status[data-router-id="${routerId}"]`);
                const lastSeen = document.querySelector(`.router-last-seen[data-router-id="${routerId}"]`);
                
                if (statusBadge) {
                    statusBadge.classList.remove('badge-success', 'badge-danger');
                    statusBadge.classList.add(data.is_online ? 'badge-success' : 'badge-danger');
                    statusBadge.textContent = data.is_online ? 'Online' : 'Offline';
                }
                
                if (lastSeen) {
                    lastSeen.textContent = data.last_seen || 'Never';
                }
            });
    }

    // Auto-update status every 30 seconds
    setInterval(() => {
        document.querySelectorAll('.router-status').forEach(badge => {
            const routerId = badge.dataset.routerId;
            if (routerId) updateRouterStatus(routerId);
        });
    }, 30000);

    </script>
@endpush