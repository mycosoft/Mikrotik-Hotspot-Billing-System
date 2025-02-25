@extends('adminlte::page')

@section('title', 'Daily Reports')

@section('content_header')
    <h1>Daily Reports</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transactions for {{ now()->format('F j, Y') }}</h3>
                    <div class="card-tools">
                        <h4>Total: {{ number_format($totalAmount, 2) }}</h4>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('H:i') }}</td>
                                    <td>{{ $transaction->customer->name }}</td>
                                    <td>{{ $transaction->plan->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ ucfirst($transaction->payment_method) }}</td>
                                    <td>{{ $transaction->reference }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
