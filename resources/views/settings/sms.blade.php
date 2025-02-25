@extends('adminlte::page')

@section('title', 'SMS Settings')

@section('content_header')
    <h1>SMS Settings</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">SMS Gateway Configuration</h3>
            </div>
            <form action="{{ route('settings.sms.update') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="provider">SMS Provider</label>
                        <select name="provider" id="provider" class="form-control @error('provider') is-invalid @enderror">
                            <option value="egosms" {{ old('provider', $settings->provider ?? '') == 'egosms' ? 'selected' : '' }}>EgoSMS</option>
                            <option value="whatsapp" {{ old('provider', $settings->provider ?? '') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        </select>
                        @error('provider')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="api_key">API Key</label>
                        <input type="text" 
                               class="form-control @error('api_key') is-invalid @enderror" 
                               id="api_key" 
                               name="api_key" 
                               value="{{ old('api_key', $settings->api_key ?? '') }}">
                        @error('api_key')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="api_secret">API Secret</label>
                        <input type="password" 
                               class="form-control @error('api_secret') is-invalid @enderror" 
                               id="api_secret" 
                               name="api_secret" 
                               value="{{ old('api_secret', $settings->api_secret ?? '') }}">
                        @error('api_secret')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sender_id">Sender ID</label>
                        <input type="text" 
                               class="form-control @error('sender_id') is-invalid @enderror" 
                               id="sender_id" 
                               name="sender_id" 
                               value="{{ old('sender_id', $settings->sender_id ?? '') }}">
                        @error('sender_id')
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

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Test SMS</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="test_phone">Phone Number</label>
                    <input type="text" class="form-control" id="test_phone" placeholder="Enter phone number">
                </div>
                <div class="form-group">
                    <label for="test_message">Message</label>
                    <textarea class="form-control" id="test_message" rows="3" placeholder="Enter test message"></textarea>
                </div>
                <button type="button" class="btn btn-info" onclick="testSms()">Send Test Message</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function testSms() {
        const phone = $('#test_phone').val();
        const message = $('#test_message').val();

        if (!phone || !message) {
            toastr.error('Please enter both phone number and message');
            return;
        }

        $.post('{{ route('settings.sms.test') }}', {
            _token: '{{ csrf_token() }}',
            test_phone: phone,
            test_message: message
        })
        .done(function(response) {
            toastr.success('Test message sent successfully');
        })
        .fail(function(error) {
            toastr.error(error.responseJSON?.error || 'Failed to send test message');
        });
    }
</script>
@stop 