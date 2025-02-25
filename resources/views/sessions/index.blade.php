@extends('adminlte::page')

@section('title', 'Active Sessions')

@section('content_header')
    <h1>Active Sessions</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped" id="sessions-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Router</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>Uptime</th>
                        <th>Data Usage</th>
                        <th>Started At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#sessions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sessions.index') }}",
                columns: [
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'router_name', name: 'router_name' },
                    { data: 'ip_address', name: 'ip_address' },
                    { data: 'mac_address', name: 'mac_address' },
                    { data: 'uptime', name: 'uptime' },
                    { data: 'data_usage', name: 'data_usage' },
                    { data: 'started_at', name: 'started_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });
        });

        function disconnectSession(sessionId) {
            if (confirm('Are you sure you want to disconnect this session?')) {
                $.ajax({
                    url: `/sessions/${sessionId}/disconnect`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#sessions-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON.message || 'Failed to disconnect session');
                    }
                });
            }
        }
    </script>
@stop
