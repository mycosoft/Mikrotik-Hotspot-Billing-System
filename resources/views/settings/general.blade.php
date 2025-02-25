@extends('adminlte::page')

@section('title', 'General Settings')

@section('content_header')
    <h1>General Settings</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Settings</h3>
            </div>
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" 
                               class="form-control @error('company_name') is-invalid @enderror" 
                               id="company_name" 
                               name="company_name" 
                               value="{{ old('company_name', $settings['company_name'] ?? config('app.name')) }}"
                               required>
                        <small class="form-text text-muted">This name will be used throughout the system.</small>
                        @error('company_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select name="timezone" id="timezone" class="form-control @error('timezone') is-invalid @enderror">
                            @foreach(timezone_identifiers_list() as $timezone)
                                <option value="{{ $timezone }}" 
                                        {{ old('timezone', config('app.timezone')) == $timezone ? 'selected' : '' }}>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                        @error('timezone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <input type="text" 
                               class="form-control @error('currency') is-invalid @enderror" 
                               id="currency" 
                               name="currency" 
                               value="{{ old('currency', 'UGX') }}">
                        @error('currency')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop 