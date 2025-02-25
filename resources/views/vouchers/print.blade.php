@extends('adminlte::page')

@section('title', 'Print Vouchers')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Print Vouchers</h1>
        <a href="{{ route('vouchers.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('vouchers.print') }}" method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>From ID</label>
                        <input type="number" name="from_id" class="form-control" value="{{ request('from_id', 1) }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Limit</label>
                        <input type="number" name="limit" class="form-control" value="{{ request('limit', 20) }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Router</label>
                        <select name="router" class="form-control">
                            <option value="">All Routers</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->name }}" {{ request('router') == $router->name ? 'selected' : '' }}
                                    class="{{ $router->is_online ? 'text-success' : 'text-danger' }}">
                                    {{ $router->name }} ({{ $router->ip }})
                                    {{ $router->is_online ? '- Online' : '- Offline' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Plan</label>
                        <select name="plan_id" class="form-control">
                            <option value="">All Plans</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Vouchers Per Line</label>
                        <input type="number" name="per_line" class="form-control" value="{{ request('per_line', 4) }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>PageBreak after</label>
                        <input type="number" name="page_break" class="form-control" value="{{ request('page_break', 12) }}">
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if($vouchers->count() > 0)
                        <button type="button" class="btn btn-success" id="printBtn">
                            <i class="fas fa-print"></i> Print
                        </button>
                    @endif
                </div>
            </div>
        </form>

        @if($vouchers->count() > 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Print side by side, it will be easy to cut<br>
                Showing {{ $vouchers->count() }} vouchers from ID {{ request('from_id', 1) }}, limit {{ request('limit', 20) }} vouchers
            </div>

            <div class="table-responsive" id="vouchers-grid">
                <table class="table table-bordered">
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach($vouchers->chunk(request('per_line', 4)) as $row)
                            <tr>
                                @foreach($row as $voucher)
                                    <td class="voucher-cell">
                                        <div class="voucher-card">
                                            <div class="company-name">{{ config('app.name') }}</div>
                                            <div class="voucher-content">
                                                <div class="qr-code">
                                                    {!! QrCode::size(80)->generate($voucher->code) !!}
                                                </div>
                                                <div class="voucher-details">
                                                    <div class="price">UGX {{ number_format($voucher->plan->price) }}</div>
                                                    <div class="code">{{ $voucher->code }}</div>
                                                    <div class="plan-type">{{ $voucher->plan->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                                @for($i = $row->count(); $i < request('per_line', 4); $i++)
                                    <td></td>
                                @endfor
                            </tr>
                            @php 
                                $counter += $row->count();
                                if($counter % request('page_break', 12) == 0 && !$loop->last) {
                                    echo '<tr class="page-break"><td colspan="' . request('per_line', 4) . '"></td></tr>';
                                }
                            @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .voucher-cell {
        padding: 10px;
        width: {{ 100/request('per_line', 4) }}%;
    }
    
    .voucher-card {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }
    
    .company-name {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .voucher-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .qr-code {
        width: 80px;
    }
    
    .voucher-details {
        text-align: right;
        padding-left: 10px;
    }
    
    .price {
        font-size: 16px;
        font-weight: bold;
    }
    
    .code {
        font-family: monospace;
        font-size: 14px;
        margin: 5px 0;
    }
    
    .plan-type {
        font-size: 12px;
    }
    
    @media print {
        .card-header, form, .alert {
            display: none !important;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .voucher-card {
            page-break-inside: avoid;
        }

        .card {
            border: none !important;
        }

        .card-body {
            padding: 0 !important;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Only trigger print when print button is clicked
        $('#printBtn').click(function() {
            window.print();
        });
    });
</script>
@stop
