@extends('adminlte::page')

@section('title', 'Generate Vouchers')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Generate Vouchers</h1>
        <a href="{{ route('vouchers.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Generate Vouchers</h3>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('vouchers.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-2 control-label">Voucher Type</label>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="custom-control custom-radio mr-4">
                                    <input type="radio" id="type_hotspot" name="type" value="Hotspot" 
                                           class="custom-control-input" 
                                           {{ old('type', 'Hotspot') == 'Hotspot' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type_hotspot">Hotspot</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type_pppoe" name="type" value="PPPOE" 
                                           class="custom-control-input"
                                           {{ old('type') == 'PPPOE' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type_pppoe">PPPOE</label>
                                </div>
                            </div>
                            @error('type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 control-label">Router</label>
                        <div class="col-md-6">
                            <select class="form-control @error('router') is-invalid @enderror" 
                                    id="router" 
                                    name="router"
                                    required>
                                <option value="">Select a router</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" 
                                            {{ old('router') == $router->id ? 'selected' : '' }}
                                            class="{{ $router->is_online ? 'text-success' : 'text-danger' }}">
                                        {{ $router->name }} ({{ $router->ip }}) 
                                        {{ $router->is_online ? '- Online' : '- Offline' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('router')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <p class="help-block text-muted">Select the router to generate vouchers for</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 control-label">Internet Plan</label>
                        <div class="col-md-6">
                            <select class="form-control @error('plan_id') is-invalid @enderror" 
                                    id="plan_id" 
                                    name="plan_id"
                                    required>
                                <option value="">Select a plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" 
                                            {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <p class="help-block text-muted">Select the internet plan for these vouchers</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 control-label">Number of Vouchers</label>
                        <div class="col-md-6">
                            <input type="number" 
                                   class="form-control @error('number_of_vouchers') is-invalid @enderror" 
                                   id="number_of_vouchers" 
                                   name="number_of_vouchers" 
                                   value="{{ old('number_of_vouchers', 10) }}"
                                   min="1"
                                   max="100"
                                   required>
                            @error('number_of_vouchers')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <p class="help-block text-muted">Number of vouchers to generate (max: 100)</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 control-label">Voucher Code Format</label>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="format_numbers" name="voucher_format" value="numbers" 
                                               class="custom-control-input" 
                                               {{ old('voucher_format', 'numbers') == 'numbers' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="format_numbers">Numbers</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="format_up" name="voucher_format" value="up" 
                                               class="custom-control-input"
                                               {{ old('voucher_format') == 'up' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="format_up">Uppercase</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="format_low" name="voucher_format" value="low" 
                                               class="custom-control-input"
                                               {{ old('voucher_format') == 'low' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="format_low">Lowercase</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="format_rand" name="voucher_format" value="rand" 
                                               class="custom-control-input"
                                               {{ old('voucher_format') == 'rand' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="format_rand">Random</label>
                                    </div>
                                </div>
                            </div>
                            @error('voucher_format')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <p class="help-block text-muted">Choose the format for generated voucher codes</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 control-label">Code Prefix</label>
                        <div class="col-md-6">
                            <input type="text" 
                                   class="form-control @error('prefix') is-invalid @enderror" 
                                   id="prefix" 
                                   name="prefix" 
                                   value="{{ old('prefix') }}"
                                   maxlength="10"
                                   placeholder="e.g., HOT-">
                            @error('prefix')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <p class="help-block text-muted">Optional prefix for voucher codes (max: 10 characters)</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 control-label">Code Length</label>
                        <div class="col-md-6">
                            <input type="number" 
                                   class="form-control @error('length_code') is-invalid @enderror" 
                                   id="length_code" 
                                   name="length_code" 
                                   value="{{ old('length_code', 12) }}"
                                   min="6"
                                   max="20"
                                   required>
                            @error('length_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <p class="help-block text-muted">Length of generated voucher codes (min: 6, max: 20)</p>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="col-lg-offset-2 col-lg-10">
                        <button class="btn btn-primary" type="submit">Generate Vouchers</button>
                        <a href="{{ route('vouchers.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.custom-radio {
    margin-bottom: 10px;
}
.help-block {
    margin-top: 5px;
    font-size: 13px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize select2 if needed
    if ($.fn.select2) {
        $('#router, #plan_id').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
});
</script>
@stop
