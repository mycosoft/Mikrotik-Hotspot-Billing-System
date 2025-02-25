@extends('adminlte::page')

@section('title', 'Bandwidth Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Bandwidth Details: {{ $bandwidth->name }}</h1>
        <div>
            <a href="{{ route('bandwidths.edit', $bandwidth) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('bandwidths.destroy', $bandwidth) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete this bandwidth?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bandwidth Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>{{ $bandwidth->name }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                <span class="badge {{ $bandwidth->type == 'Dedicated' ? 'bg-success' : 'bg-info' }}">
                                    {{ $bandwidth->type }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Upload Speed</th>
                            <td>{{ $bandwidth->upload_speed ? $bandwidth->upload_speed . ' Kbps' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Download Speed</th>
                            <td>{{ $bandwidth->download_speed ? $bandwidth->download_speed . ' Kbps' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge {{ $bandwidth->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $bandwidth->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $bandwidth->description ?? 'No description' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Associated Internet Plans</h3>
                </div>
                <div class="card-body">
                    @if($bandwidth->internetPlans->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Plan Name</th>
                                    <th>Validity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bandwidth->internetPlans as $plan)
                                    <tr>
                                        <td>
                                            <a href="{{ route('internet-plans.show', $plan) }}">
                                                {{ $plan->name }}
                                            </a>
                                        </td>
                                        <td>{{ $plan->validity_days }} days</td>
                                        <td>{{ number_format($plan->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-center text-muted">No internet plans associated with this bandwidth.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
