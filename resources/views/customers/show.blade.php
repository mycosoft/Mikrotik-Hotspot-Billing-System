@extends('adminlte::page')

@section('title', 'Customer Details')

@section('content_header')
    <h1>Customer Details</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-{{ $customer->status === 'active' ? 'primary' : 'danger' }}">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="https://robohash.org/{{ $customer->id }}?set=set3&size=100x100&bgset=bg1" 
                         alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $customer->name }}</h3>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b> 
                        <span class="float-right badge badge-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Username</b> <span class="float-right">{{ $customer->username }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Phone</b> <span class="float-right">{{ $customer->phone }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Email</b> <span class="float-right">{{ $customer->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Address</b> <span class="float-right">{{ $customer->address }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>City</b> <span class="float-right">{{ $customer->city }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Balance</b> <span class="float-right">${{ number_format($customer->balance, 2) }}</span>
                    </li>
                    @if($customer->service_type === 'pppoe')
                        <li class="list-group-item">
                            <b>PPPoE Username</b> <span class="float-right">{{ $customer->pppoe_username }}</span>
                        </li>
                        @can('manage customers')
                        <li class="list-group-item">
                            <b>PPPoE Password</b> 
                            <input type="password" value="{{ $customer->pppoe_password }}" 
                                   class="float-right border-0 text-right bg-transparent" 
                                   style="cursor: pointer;"
                                   onmouseleave="this.type = 'password'" 
                                   onmouseenter="this.type = 'text'" 
                                   onclick="this.select()" 
                                   readonly>
                        </li>
                        @endcan
                        @if($customer->ip_address)
                        <li class="list-group-item">
                            <b>IP Address</b> <span class="float-right">{{ $customer->ip_address }}</span>
                        </li>
                        @endif
                    @endif
                    <li class="list-group-item">
                        <b>Service Type</b> <span class="float-right">{{ ucfirst($customer->service_type) }}</span>
                    </li>
                </ul>

                <div class="row">
                    <div class="col-md-4">
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-block">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('customers.recharge', $customer) }}" class="btn btn-success btn-block">
                            <i class="fas fa-money-bill"></i> Recharge
                        </a>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('customers.toggle', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $customer->status === 'active' ? 'warning' : 'success' }} btn-block">
                                <i class="fas fa-power-off"></i> 
                                {{ $customer->status === 'active' ? 'Suspend' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="#transactions" data-toggle="tab">
                            Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#sessions" data-toggle="tab">
                            Active Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vouchers" data-toggle="tab">
                            Vouchers
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="transactions">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->transactions()->latest()->take(10)->get() as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($transaction->amount, 2) }}</td>
                                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                                            <td>{{ $transaction->reference }}</td>
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

                    <div class="tab-pane" id="sessions">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Start Time</th>
                                        <th>IP Address</th>
                                        <th>MAC Address</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeSessions as $session)
                                        <tr>
                                            <td>{{ $session->start_time }}</td>
                                            <td>{{ $session->ip_address }}</td>
                                            <td>{{ $session->mac_address }}</td>
                                            <td>{{ formatBytes($session->bytes_up) }}</td>
                                            <td>{{ formatBytes($session->bytes_down) }}</td>
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

                    <div class="tab-pane" id="vouchers">
                        <div class="table-responsive">
                            <table class="table">
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
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .nav-pills .nav-link.active {
        background-color: #007bff;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Activate tab from hash if present
        var hash = window.location.hash;
        if (hash) {
            $('.nav-pills a[href="' + hash + '"]').tab('show');
        }

        // Update hash on tab change
        $('.nav-pills a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    });
</script>
@stop
