<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Member Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 20px;
        }
        .panel-heading {
            background-color: #3498db !important;
            color: white !important;
            border: none !important;
        }
        .panel-primary {
            border-color: #3498db;
        }
        .btn-success {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        .btn-success:hover {
            background-color: #219a52;
            border-color: #219a52;
        }
        a {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Member Dashboard</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Welcome, {{ $customer->name }}</h4>
                                <hr>
                            </div>
                        </div>

                        @if($activeSession)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Active Session</h3>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-striped">
                                                <tr>
                                                    <td>Username</td>
                                                    <td>{{ $customer->username }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Plan</td>
                                                    <td>{{ $activeSession->voucher->plan->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Time Left</td>
                                                    <td>{{ $activeSession->expires_at->diffForHumans(null, true) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Data Left</td>
                                                    <td>
                                                        @if($activeSession->voucher->plan->data_limit)
                                                            {{ formatBytes($activeSession->voucher->plan->data_limit) }}
                                                        @else
                                                            Unlimited
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>MAC Address</td>
                                                    <td>{{ $activeSession->mac_address }}</td>
                                                </tr>
                                                <tr>
                                                    <td>IP Address</td>
                                                    <td>{{ $activeSession->ip_address }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No active session found. Please <a href="{{ route('voucher.activate') }}">activate a voucher</a>.
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('member.logout') }}" class="btn btn-danger">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
