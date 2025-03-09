@extends('adminlte::page')

@section('title', 'SMTP Settings')

@section('content_header')
    <h1>SMTP Settings</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('settings.smtp.update') }}" method="POST">
                @csrf
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Email Server Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" class="form-control @error('smtp_host') is-invalid @enderror" 
                                   id="smtp_host" name="smtp_host" 
                                   value="{{ old('smtp_host', $settings['smtp_host']->value ?? '') }}"
                                   placeholder="smtp.gmail.com">
                            @error('smtp_host')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="smtp_port">SMTP Port</label>
                            <input type="number" class="form-control @error('smtp_port') is-invalid @enderror" 
                                   id="smtp_port" name="smtp_port" 
                                   value="{{ old('smtp_port', $settings['smtp_port']->value ?? '587') }}"
                                   placeholder="587">
                            @error('smtp_port')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="smtp_username">SMTP Username</label>
                            <input type="text" class="form-control @error('smtp_username') is-invalid @enderror" 
                                   id="smtp_username" name="smtp_username" 
                                   value="{{ old('smtp_username', $settings['smtp_username']->value ?? '') }}"
                                   placeholder="your@email.com">
                            @error('smtp_username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" class="form-control @error('smtp_password') is-invalid @enderror" 
                                   id="smtp_password" name="smtp_password" 
                                   value="{{ old('smtp_password', $settings['smtp_password']->value ?? '') }}"
                                   placeholder="Enter password">
                            @error('smtp_password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                For Gmail, you need to use an App Password. 
                                <a href="https://support.google.com/accounts/answer/185833" target="_blank">Learn more</a>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="smtp_encryption">Encryption</label>
                            <select class="form-control @error('smtp_encryption') is-invalid @enderror" 
                                    id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" {{ (old('smtp_encryption', $settings['smtp_encryption']->value ?? '') == 'tls') ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ (old('smtp_encryption', $settings['smtp_encryption']->value ?? '') == 'ssl') ? 'selected' : '' }}>SSL</option>
                            </select>
                            @error('smtp_encryption')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Test Email Configuration</h3>
                </div>
                <form action="{{ route('settings.smtp.test') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="test_email">Send Test Email To</label>
                            <input type="email" class="form-control @error('test_email') is-invalid @enderror" 
                                   id="test_email" name="test_email" 
                                   placeholder="Enter email address">
                            @error('test_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </div>
                </form>
            </div>


        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
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
