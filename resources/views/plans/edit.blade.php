@extends('adminlte::page')

@section('title', 'Edit Internet Plan')

@section('content_header')
    <h1>Edit Internet Plan: {{ $plan->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form action="{{ route('plans.update', $plan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('plans.form')
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                        <a href="{{ route('plans.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Information</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Created</dt>
                        <dd>{{ $plan->created_at->format('Y-m-d H:i') }}</dd>

                        <dt>Last Updated</dt>
                        <dd>{{ $plan->updated_at->format('Y-m-d H:i') }}</dd>

                        <dt>Router</dt>
                        <dd>{{ $plan->router->name }}</dd>

                        <dt>Bandwidth</dt>
                        <dd>{{ $plan->bandwidth->name }}</dd>
                    </dl>
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
                theme: 'bootstrap4',
                width: '100%'
            });

            // Initialize select2
            $('#bandwidth_id, #router_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            });

            // Handle plan type change
            function handlePlanTypeChange() {
                const planType = $('#type').val();
                const limitTypeGroup = $('#limitTypeGroup');
                const timeLimitGroup = $('#timeLimitGroup');
                const dataLimitGroup = $('#dataLimitGroup');

                if (planType === 'unlimited') {
                    limitTypeGroup.hide();
                    timeLimitGroup.hide();
                    dataLimitGroup.hide();
                    $('#limit_type').val('');
                    $('#time_limit').val('');
                    $('#data_limit').val('');
                } else {
                    limitTypeGroup.show();
                    handleLimitTypeChange();
                }
            }

            // Handle limit type change
            function handleLimitTypeChange() {
                const limitType = $('#limit_type').val();
                const timeLimitGroup = $('#timeLimitGroup');
                const dataLimitGroup = $('#dataLimitGroup');

                switch (limitType) {
                    case 'time':
                        timeLimitGroup.show();
                        dataLimitGroup.hide();
                        $('#data_limit').val('');
                        break;
                    case 'data':
                        timeLimitGroup.hide();
                        dataLimitGroup.show();
                        $('#time_limit').val('');
                        break;
                    case 'both':
                        timeLimitGroup.show();
                        dataLimitGroup.show();
                        break;
                    default:
                        timeLimitGroup.hide();
                        dataLimitGroup.hide();
                        $('#time_limit').val('');
                        $('#data_limit').val('');
                }
            }

            // Attach event handlers
            $('#type').change(handlePlanTypeChange);
            $('#limit_type').change(handleLimitTypeChange);

            // Initial setup
            handlePlanTypeChange();
        });
    </script>
@stop
