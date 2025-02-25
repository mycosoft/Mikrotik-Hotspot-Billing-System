@extends('adminlte::page')

@section('title', 'Recharge Customer')

@section('content_header')
    <h1>Recharge Customer: {{ $customer->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Balance</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-wallet"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Balance</span>
                                    <span class="info-box-number">{{ number_format($customer->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('customers.recharge.store', $customer) }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="amount">Amount to Add</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       value="{{ old('amount') }}"
                                       step="0.01"
                                       min="0"
                                       required>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" 
                                    name="payment_method"
                                    required>
                                <option value="">Select Payment Method</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reference">Payment Reference</label>
                            <input type="text" 
                                   class="form-control @error('reference') is-invalid @enderror" 
                                   id="reference" 
                                   name="reference" 
                                   value="{{ old('reference') }}"
                                   placeholder="Transaction ID, Receipt Number, etc.">
                            @error('reference')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Any additional notes about this transaction">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Process Recharge</button>
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Transactions</h3>
                </div>
                <div class="card-body p-0">
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
                                @forelse($customer->transactions()->latest()->take(5)->get() as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($transaction->type == 'credit')
                                                <span class="badge badge-success">Credit</span>
                                            @else
                                                <span class="badge badge-danger">Debit</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($transaction->amount, 2) }}</td>
                                        <td>{{ ucfirst($transaction->payment_method) }}</td>
                                        <td>{{ $transaction->reference }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No recent transactions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
