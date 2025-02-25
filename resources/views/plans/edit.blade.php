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
                theme: 'bootstrap4'
            });
        });
    </script>
@stop
