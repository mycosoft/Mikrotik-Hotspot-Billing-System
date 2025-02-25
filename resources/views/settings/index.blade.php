@extends('adminlte::page')

@section('title', 'Settings')

@section('content_header')
    <h1>System Settings</h1>
@stop

@section('content')
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">General Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" 
                                   value="{{ old('company_name', $settings['company_name']->value ?? '') }}">
                            @error('company_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_logo">Company Logo</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('logo') is-invalid @enderror" 
                                           id="company_logo" name="logo" accept="image/*">
                                    <label class="custom-file-label" for="company_logo">Choose file</label>
                                </div>
                            </div>
                            @if(isset($settings['logo']))
                                <img src="{{ asset($settings['logo']->value) }}" alt="Company Logo" class="mt-2" style="max-height: 50px">
                            @endif
                            @error('logo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_address">Company Address</label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                      id="company_address" name="company_address" 
                                      rows="3">{{ old('company_address', $settings['company_address']->value ?? '') }}</textarea>
                            @error('company_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_phone">Phone Number</label>
                            <input type="text" class="form-control @error('company_phone') is-invalid @enderror" 
                                   id="company_phone" name="company_phone" 
                                   value="{{ old('company_phone', $settings['company_phone']->value ?? '') }}">
                            @error('company_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_email">Email Address</label>
                            <input type="email" class="form-control @error('company_email') is-invalid @enderror" 
                                   id="company_email" name="company_email" 
                                   value="{{ old('company_email', $settings['company_email']->value ?? '') }}">
                            @error('company_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Invoice Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <input type="text" class="form-control @error('currency') is-invalid @enderror" 
                                   id="currency" name="currency" 
                                   value="{{ old('currency', $settings['currency']->value ?? '') }}">
                            @error('currency')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tax_rate">Tax Rate (%)</label>
                            <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" 
                                   id="tax_rate" name="tax_rate" min="0" max="100" step="0.01"
                                   value="{{ old('tax_rate', $settings['tax_rate']->value ?? '0') }}">
                            @error('tax_rate')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="invoice_prefix">Invoice Prefix</label>
                            <input type="text" class="form-control @error('invoice_prefix') is-invalid @enderror" 
                                   id="invoice_prefix" name="invoice_prefix" 
                                   value="{{ old('invoice_prefix', $settings['invoice_prefix']->value ?? '') }}">
                            @error('invoice_prefix')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="invoice_footer">Invoice Footer</label>
                            <textarea class="form-control @error('invoice_footer') is-invalid @enderror" 
                                      id="invoice_footer" name="invoice_footer" 
                                      rows="3">{{ old('invoice_footer', $settings['invoice_footer']->value ?? '') }}</textarea>
                            @error('invoice_footer')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">System Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="theme">Theme</label>
                            <select class="form-control @error('theme') is-invalid @enderror" 
                                    id="theme" name="theme">
                                <option value="default" {{ (old('theme', $settings['theme']->value ?? '') == 'default') ? 'selected' : '' }}>Default</option>
                                <option value="dark" {{ (old('theme', $settings['theme']->value ?? '') == 'dark') ? 'selected' : '' }}>Dark</option>
                            </select>
                            @error('theme')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (minutes)</label>
                            <input type="number" class="form-control @error('session_timeout') is-invalid @enderror" 
                                   id="session_timeout" name="session_timeout" min="1" max="1440"
                                   value="{{ old('session_timeout', $settings['session_timeout']->value ?? '120') }}">
                            @error('session_timeout')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" 
                                       id="enable_registration" name="enable_registration" value="1"
                                       {{ (old('enable_registration', $settings['enable_registration']->value ?? '0') == '1') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="enable_registration">Enable User Registration</label>
                            </div>
                            @error('enable_registration')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="default_role">Default User Role</label>
                            <input type="text" class="form-control @error('default_role') is-invalid @enderror" 
                                   id="default_role" name="default_role" 
                                   value="{{ old('default_role', $settings['default_role']->value ?? 'customer') }}">
                            @error('default_role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Email Settings</h3>
                        <div class="card-tools">
                            <a href="{{ route('settings.smtp') }}" class="btn btn-tool">
                                <i class="fas fa-envelope"></i> Configure SMTP
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Configure email settings to enable system notifications and customer communications.</p>
                        <a href="{{ route('settings.smtp') }}" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Configure SMTP Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize custom file input
            bsCustomFileInput.init();

            // Show success message if exists
            @if(session('success'))
                $(document).Toasts('create', {
                    class: 'bg-success',
                    title: 'Success',
                    body: '{{ session('success') }}'
                });
            @endif

            // Show error message if exists
            @if(session('error'))
                $(document).Toasts('create', {
                    class: 'bg-danger',
                    title: 'Error',
                    body: '{{ session('error') }}'
                });
            @endif
        });
    </script>
@stop
