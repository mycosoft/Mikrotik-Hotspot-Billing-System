@extends('adminlte::page')

@section('title', 'Plan Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Plan Details: {{ $plan->name }}</h1>
        <div>
            <a href="{{ route('plans.edit', $plan) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @if($plan->activeVouchers()->count() === 0)
                <form action="{{ route('plans.destroy', $plan) }}" 
                      method="POST" 
                      class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Name</th>
                            <td>{{ $plan->name }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                @if($plan->type === 'unlimited')
                                    <span class="badge badge-success">Unlimited</span>
                                @else
                                    <span class="badge badge-info">
                                        Limited
                                        @if($plan->limit_type === 'time')
                                            ({{ $plan->formatted_time_limit }})
                                        @elseif($plan->limit_type === 'data')
                                            ({{ $plan->formatted_data_limit }})
                                        @else
                                            ({{ $plan->formatted_time_limit }} / {{ $plan->formatted_data_limit }})
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <td>{{ number_format($plan->price, 2) }} UGX</td>
                        </tr>
                        <tr>
                            <th>Validity</th>
                            <td>{{ $plan->validity_days }} days</td>
                        </tr>
                        <tr>
                            <th>Simultaneous Sessions</th>
                            <td>{{ $plan->simultaneous_sessions }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bandwidth Settings</h3>
                </div>
                <div class="card-body">
                    @if($plan->bandwidth)
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Bandwidth Name</th>
                                <td>{{ $plan->bandwidth->name }}</td>
                            </tr>
                            <tr>
                                <th>Download Speed</th>
                                <td>{{ $plan->bandwidth->download_speed }} Kbps</td>
                            </tr>
                            <tr>
                                <th>Upload Speed</th>
                                <td>{{ $plan->bandwidth->upload_speed }} Kbps</td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td>{{ ucfirst($plan->bandwidth->type) }}</td>
                            </tr>
                        </table>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No bandwidth profile assigned to this plan.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Router Information</h3>
                </div>
                <div class="card-body">
                    @if($plan->router)
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Router Name</th>
                                <td>{{ $plan->router->name }}</td>
                            </tr>
                            <tr>
                                <th>IP Address</th>
                                <td>{{ $plan->router->ip_address }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-{{ $plan->router->is_active ? 'success' : 'danger' }}">
                                        {{ $plan->router->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No router assigned to this plan.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Usage Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-ticket-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active Vouchers</span>
                                    <span class="info-box-number">{{ $plan->activeVouchers()->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Vouchers</span>
                                    <span class="info-box-number">{{ $plan->vouchers()->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($plan->activeVouchers()->count() > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This plan has {{ $plan->activeVouchers()->count() }} active vouchers. 
                            Any changes to the plan will affect these users.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
