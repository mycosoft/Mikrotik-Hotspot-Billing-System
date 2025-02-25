@extends('adminlte::page')

@section('title', 'Create Internet Plan')

@section('content_header')
    <h1>Create New Internet Plan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form action="{{ route('plans.store') }}" method="POST">
                    @csrf
                    @include('plans.form')
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Plan</button>
                        <a href="{{ route('plans.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <p>Create a new internet plan by filling out the form:</p>
                    <ul>
                        <li>Enter a descriptive name for the plan</li>
                        <li>Choose between Limited or Unlimited type</li>
                        <li>For Limited plans, specify the time and/or data limits</li>
                        <li>Set the price in UGX</li>
                        <li>Select the validity period in days</li>
                        <li>Choose a bandwidth profile for speed limits</li>
                        <li>Select which router this plan will be available on</li>
                        <li>Set the number of allowed simultaneous connections</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap',
                width: '100%'
            });

            // Handle limit type visibility
            function updateLimitFields() {
                var planType = $('input[name="type"]:checked').val();
                var limitType = $('input[name="limit_type"]:checked').val();
                
                if (planType === 'unlimited') {
                    $('.time-limit-group, .data-limit-group').hide();
                    $('#time_limit, #data_limit').prop('required', false);
                } else {
                    if (limitType === 'time') {
                        $('.time-limit-group').show();
                        $('.data-limit-group').hide();
                        $('#time_limit').prop('required', true);
                        $('#data_limit').prop('required', false);
                    } else if (limitType === 'data') {
                        $('.time-limit-group').hide();
                        $('.data-limit-group').show();
                        $('#time_limit').prop('required', false);
                        $('#data_limit').prop('required', true);
                    } else if (limitType === 'both') {
                        $('.time-limit-group, .data-limit-group').show();
                        $('#time_limit, #data_limit').prop('required', true);
                    }
                }
            }

            // Initial setup
            updateLimitFields();

            // Update on change
            $('input[name="type"], .limit-type').change(function() {
                updateLimitFields();
            });
        });
    </script>
@stop
