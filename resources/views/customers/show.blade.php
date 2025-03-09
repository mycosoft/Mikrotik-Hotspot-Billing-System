@extends('adminlte::page')

@section('title', 'Customer Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Customer Details</h1>
        <div>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit Customer
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm ml-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="{{ $customer->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($customer->name) }}" 
                         alt="Customer profile picture">
                </div>
                <h3 class="profile-username text-center">{{ $customer->name }}</h3>
                <p class="text-muted text-center">{{ $customer->username }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b>
                        <span class="float-right">
                            <span class="badge badge-{{ $customer->is_active ? 'success' : 'danger' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Last Login</b>
                        <span class="float-right">
                            {{ $customer->last_login ? $customer->last_login->diffForHumans() : 'Never' }}
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Service Type</b>
                        <span class="float-right badge badge-info">{{ $customer->service_type }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Balance</b>
                        <span class="float-right">UGX {{ number_format($customer->balance, 0) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Active Sessions</b>
                        <span class="float-right">{{ $customer->active_sessions_count }}</span>
                    </li>
                </ul>

                <div class="btn-group w-100 mb-2">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#rechargeModal">
                        <i class="fas fa-money-bill"></i> Recharge
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="toggleStatus({{ $customer->id }})">
                        <i class="fas fa-power-off"></i> {{ $customer->is_active ? 'Disable' : 'Enable' }}
                    </button>
                </div>
                <div class="btn-group w-100">
                    <button type="button" class="btn btn-info btn-sm" onclick="syncToMikrotik({{ $customer->id }})">
                        <i class="fas fa-sync"></i> Sync to Mikrotik
                    </button>
                    <a href="{{ route('messages.single', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-envelope"></i> Send Message
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="#details" data-toggle="tab">Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#sessions" data-toggle="tab">Active Sessions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vouchers" data-toggle="tab">Vouchers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#transactions" data-toggle="tab">Transactions</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    <!-- Details Tab -->
                    <div class="tab-pane active" id="details">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Personal Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tr>
                                                <th>Full Name</th>
                                                <td>{{ $customer->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Username</th>
                                                <td>{{ $customer->username }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $customer->email }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone</th>
                                                <td>{{ $customer->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th>Address</th>
                                                <td>{{ $customer->address }}</td>
                                            </tr>
                                            <tr>
                                                <th>Created On</th>
                                                <td>{{ $customer->created_at->format('M d, Y H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Last Login</th>
                                                <td>{{ $customer->last_login ? $customer->last_login->format('M d, Y H:i:s') : 'Never' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Service Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tr>
                                                <th>Service Type</th>
                                                <td>{{ $customer->service_type }}</td>
                                            </tr>
                                            <tr>
                                                <th>Current Package</th>
                                                <td>{{ $customer->currentPlan->name ?? 'No active plan' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Package Speed</th>
                                                <td>{{ $customer->currentPlan ? $customer->currentPlan->download_speed . '/' . $customer->currentPlan->upload_speed . ' Mbps' : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Balance</th>
                                                <td>UGX {{ number_format($customer->balance, 0) }}</td>
                                            </tr>
                                            <tr>
                                                <th>IP Address</th>
                                                <td>{{ $customer->ip_address ?? 'Dynamic' }}</td>
                                            </tr>
                                            <tr>
                                                <th>MAC Address</th>
                                                <td>{{ $customer->mac_address ?? 'Not set' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span class="badge badge-{{ $customer->is_active ? 'success' : 'danger' }}">
                                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sessions Tab -->
                    <div class="tab-pane" id="sessions">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Router</th>
                                        <th>IP Address</th>
                                        <th>MAC Address</th>
                                        <th>Started At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->activeSessions as $session)
                                        <tr>
                                            <td>{{ $session->router->name }}</td>
                                            <td>{{ $session->ip_address }}</td>
                                            <td>{{ $session->mac_address }}</td>
                                            <td>{{ $session->started_at }}</td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="disconnectSession('{{ $session->id }}')">
                                                    <i class="fas fa-times"></i> Disconnect
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No active sessions</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Vouchers Tab -->
                    <div class="tab-pane" id="vouchers">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Plan</th>
                                        <th>Used At</th>
                                        <th>Expires At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->vouchers as $voucher)
                                        <tr>
                                            <td>{{ $voucher->code }}</td>
                                            <td>{{ $voucher->plan->name }}</td>
                                            <td>{{ $voucher->used_at }}</td>
                                            <td>{{ $voucher->expires_at }}</td>
                                            <td>
                                                <span class="badge badge-{{ $voucher->isExpired() ? 'danger' : 'success' }}">
                                                    {{ $voucher->isExpired() ? 'Expired' : 'Active' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No vouchers found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Transactions Tab -->
                    <div class="tab-pane" id="transactions">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at }}</td>
                                            <td>{{ $transaction->type }}</td>
                                            <td>UGX {{ number_format($transaction->amount, 0) }}</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>
                                                <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No transactions found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recharge Modal -->
<div class="modal fade" id="rechargeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('customers.recharge.store', $customer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Recharge Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Recharge</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .profile-user-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }
</style>
@stop

@section('js')
<script>
    function toggleStatus(customerId) {
        if (confirm('Are you sure you want to change this customer\'s status?')) {
            $.post(`/customers/${customerId}/toggle`, {
                _token: '{{ csrf_token() }}'
            }).done(function() {
                location.reload();
            });
        }
    }

    function disconnectSession(sessionId) {
        if (confirm('Are you sure you want to disconnect this session?')) {
            $.post(`/sessions/${sessionId}/disconnect`, {
                _token: '{{ csrf_token() }}'
            }).done(function() {
                location.reload();
            });
        }
    }

    function syncToMikrotik(customerId) {
        if (confirm('Are you sure you want to sync this customer to Mikrotik?')) {
            $.post(`/customers/${customerId}/sync`, {
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                toastr.success('Customer synced successfully');
            }).fail(function(error) {
                toastr.error('Failed to sync customer');
            });
        }
    }
</script>
@stop
