@extends('adminlte::page')

@section('title', isset($router) ? 'Edit Router' : 'Add Router')

@section('content_header')
    <h1>{{ isset($router) ? 'Edit Router: ' . $router->name : 'Add New Router' }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <form action="{{ isset($router) ? route('routers.update', $router) : route('routers.store') }}" 
                      method="POST">
                    @csrf
                    @if(isset($router))
                        @method('PATCH')
                    @endif
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Router Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $router->name ?? '') }}"
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ip">IP Address</label>
                            <input type="text" 
                                   class="form-control @error('ip') is-invalid @enderror" 
                                   id="ip" 
                                   name="ip" 
                                   placeholder="192.168.1.1:8728"
                                   value="{{ old('ip', $router->ip ?? '') }}"
                                   required>
                            <small class="form-text text-muted">Format: IP:PORT (e.g., 192.168.1.1:8728). Port is optional, defaults to 8728.</small>
                            @error('ip')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="username">API Username</label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $router->username ?? '') }}"
                                   required>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">API Password</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   {{ isset($router) ? '' : 'required' }}>
                            @if(isset($router))
                                <small class="form-text text-muted">Leave empty to keep current password</small>
                            @endif
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', isset($router) && $router->is_active ? 'checked' : '') }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($router) ? 'Update Router' : 'Create Router' }}
                        </button>
                        <button type="button" class="btn btn-info" onclick="testConnection()">
                            <i class="fas fa-sync"></i> Test Connection
                        </button>
                        <a href="{{ route('routers.index') }}" class="btn btn-default float-right">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <p>{{ isset($router) ? 'Edit the router settings:' : 'Add a new Mikrotik router:' }}</p>
                    <ul>
                        <li>Set a descriptive name</li>
                        <li>Enter the router's IP address and API port (default: 8728)</li>
                        <li>Provide API credentials with admin privileges</li>
                    </ul>

                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> API Access</h5>
                        <p>To enable API access on your Mikrotik router:</p>
                        <ol>
                            <li>Login to RouterOS via Winbox</li>
                            <li>Go to IP → Services</li>
                            <li>Enable the API service (port 8728)</li>
                            <li>Create a user with API and read/write access</li>
                        </ol>
                    </div>

                    @if(isset($router) && $router->activeSessions->count() > 0)
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i>
                            This router has {{ $router->activeSessions->count() }} active sessions.
                            Changes may affect connected users.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
function testConnection() {
    const data = {
        _token: '{{ csrf_token() }}',
        ip: $('#ip').val(),
        username: $('#username').val(),
        password: $('#password').val() || '{{ isset($router) ? "current" : "" }}'
    };

    // Disable button and show loading state
    const button = event.target;
    const icon = button.querySelector('i');
    button.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';

    $.ajax({
        url: '{{ route("routers.test-connection") }}',
        method: 'POST',
        data: data,
        success: function(response) {
            alert('Connection successful!');
        },
        error: function(xhr) {
            alert('Connection failed: ' + (xhr.responseJSON?.message || 'Unknown error'));
        },
        complete: function() {
            // Re-enable button and restore icon
            button.disabled = false;
            icon.className = 'fas fa-sync';
        }
    });
}
</script>
@endpush
