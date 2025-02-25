@extends('adminlte::page')

@section('title', 'Edit Customer')

@section('content_header')
    <h1>Edit Customer: {{ $customer->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $customer->name) }}"
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $customer->username) }}"
                                   required>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $customer->phone) }}"
                                   required>
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="service_type">Service Type *</label>
                            <select class="form-control @error('service_type') is-invalid @enderror" 
                                    id="service_type" 
                                    name="service_type" 
                                    required>
                                <option value="PPPoE" {{ old('service_type', $customer->service_type) == 'PPPoE' ? 'selected' : '' }}>PPPoE</option>
                                <option value="Hotspot" {{ old('service_type', $customer->service_type) == 'Hotspot' ? 'selected' : '' }}>Hotspot</option>
                                <option value="Static" {{ old('service_type', $customer->service_type) == 'Static' ? 'selected' : '' }}>Static</option>
                            </select>
                            @error('service_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="Active" {{ old('status', $customer->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status', $customer->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="Suspended" {{ old('status', $customer->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password (Leave blank to keep current)</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input type="number" 
                                           step="0.000001"
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude', $customer->latitude) }}">
                                    @error('latitude')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input type="number" 
                                           step="0.000001"
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude', $customer->longitude) }}">
                                    @error('longitude')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                </form>
            </div>

            @if($customer->activeVoucher)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Voucher</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Code</dt>
                            <dd class="col-sm-8"><code>{{ $customer->activeVoucher->code }}</code></dd>

                            <dt class="col-sm-4">Plan</dt>
                            <dd class="col-sm-8">{{ $customer->activeVoucher->plan->name }}</dd>

                            <dt class="col-sm-4">Expires</dt>
                            <dd class="col-sm-8">{{ $customer->activeVoucher->expires_at->format('Y-m-d H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign New Voucher</h3>
                </div>
                <form action="{{ route('customers.assign-voucher', $customer) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="voucher_id">Select Voucher</label>
                            <select class="form-control @error('voucher_id') is-invalid @enderror" 
                                    id="voucher_id" 
                                    name="voucher_id"
                                    required>
                                <option value="">Select a voucher</option>
                                @foreach($vouchers as $voucher)
                                    <option value="{{ $voucher->id }}">
                                        {{ $voucher->code }} ({{ $voucher->plan->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('voucher_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($customer->activeVoucher)
                            <div class="alert alert-warning">
                                <i class="icon fas fa-exclamation-triangle"></i>
                                Assigning a new voucher will deactivate the current one.
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Assign Voucher</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Active Sessions</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Router</th>
                                <th>IP Address</th>
                                <th>Connected</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->activeSessions as $session)
                                <tr>
                                    <td>{{ $session->router->name }}</td>
                                    <td>{{ $session->ip_address }}</td>
                                    <td>{{ $session->created_at->diffForHumans() }}</td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-xs btn-danger"
                                                onclick="disconnectSession('{{ $session->id }}')">
                                            Disconnect
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No active sessions</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="disconnect-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#voucher_id').select2({
                theme: 'bootstrap',
                placeholder: 'Select a voucher'
            });
        });

        function disconnectSession(sessionId) {
            if (confirm('Are you sure you want to disconnect this session?')) {
                var form = $('#disconnect-form');
                form.attr('action', '/sessions/' + sessionId);
                form.submit();
            }
        }
    </script>
@stop
