@extends('adminlte::page')

@section('title', 'Bandwidth Plans')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Bandwidth Plans</h1>
        <a href="{{ route('bandwidths.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Bandwidth
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('bandwidths.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('bandwidths.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Upload Speed</th>
                            <th>Download Speed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bandwidths as $bandwidth)
                            <tr>
                                <td>{{ $bandwidth->name }}</td>
                                <td>{{ $bandwidth->display_upload }}</td>
                                <td>{{ $bandwidth->display_download }}</td>
                                <td>
                                    <span class="status-badge badge badge-{{ $bandwidth->is_active ? 'success' : 'danger' }}"
                                          data-id="{{ $bandwidth->id }}">
                                        {{ $bandwidth->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="window.location='{{ route('bandwidths.edit', $bandwidth) }}'"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('bandwidths.destroy', $bandwidth) }}" 
                                              method="POST" 
                                              class="ms-2"
                                              onsubmit="return confirmDelete('{{ $bandwidth->name }}', {{ $bandwidth->internetPlans()->count() }});">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No bandwidths found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $bandwidths->appends(request()->input())->links() }}
        </div>
    </div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleStatus(id) {
    $.ajax({
        url: `/bandwidths/${id}/toggle`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                const badge = $(`.status-badge[data-id="${id}"]`);
                
                if (response.is_active) {
                    badge.removeClass('badge-danger').addClass('badge-success');
                    badge.text('Active');
                } else {
                    badge.removeClass('badge-success').addClass('badge-danger');
                    badge.text('Inactive');
                }
                
                toastr.success(response.message);
            }
        },
        error: function() {
            toastr.error('Failed to update status');
        }
    });
}

// Initialize tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

function confirmDelete(name, planCount) {
    if (planCount > 0) {
        Swal.fire({
            title: 'Cannot Delete',
            text: `This bandwidth "${name}" is being used by ${planCount} internet plan(s). Please remove the associations first.`,
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete bandwidth "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            return true;
        }
        return false;
    });
}
</script>
@endpush

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
.status-badge {
    padding: 8px 12px;
    transition: all 0.3s ease;
}
.status-badge:hover {
    opacity: 0.8;
}

.btn-group .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-group form {
    display: inline-flex;
}

.ms-2 {
    margin-left: 0.5rem;
}
</style>
@endpush
