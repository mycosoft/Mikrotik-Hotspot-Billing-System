@extends('adminlte::page')

@section('title', 'Send Single Message')

@section('content_header')
    <h1>Send Single Message</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Send Message to Customer</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('messages.send-single') }}" method="POST" id="singleSmsForm">
            @csrf
            <div class="form-group">
                <label>Message Type</label>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="type_sms" name="message_type" class="custom-control-input" value="sms" checked>
                    <label class="custom-control-label" for="type_sms">
                        <i class="fas fa-sms"></i> SMS
                    </label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="type_whatsapp" name="message_type" class="custom-control-input" value="whatsapp">
                    <label class="custom-control-label" for="type_whatsapp">
                        <i class="fab fa-whatsapp text-success"></i> WhatsApp
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="customer_id">Select Customer</label>
                <select class="form-control select2" id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" 
                          maxlength="160" required></textarea>
                <small class="text-muted">
                    Characters remaining: <span id="charCount">160</span>
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="/vendor/select2/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Character counter for SMS
            $('#message').on('input', function() {
                var remaining = 160 - $(this).val().length;
                $('#charCount').text(remaining);
            });
        });
    </script>
@stop