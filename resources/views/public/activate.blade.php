<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .container { max-width: 1200px; }
        .panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 30px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-text {
            color: white;
            font-size: 24px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-tabs {
            border: none;
            margin-bottom: 0;
        }
        .nav-tabs > li > a {
            border: none;
            background: rgba(255, 255, 255, 0.7);
            color: #666;
            font-size: 16px;
            font-weight: 500;
            padding: 15px 30px;
            margin-right: 5px;
            border-radius: 12px 12px 0 0;
        }
        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:focus,
        .nav-tabs > li.active > a:hover {
            border: none;
            background: white;
            color: #3498db;
        }
        .tab-content {
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-radius: 0 0 15px 15px;
            padding: 30px;
        }
        .form-control {
            border: 2px solid #e3e3e3;
            height: 45px;
            border-radius: 8px;
            font-size: 15px;
            padding: 10px 15px;
        }
        .btn-success {
            background: linear-gradient(to right, #27ae60, #2ecc71);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
        }
        .package-card {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: white;
            border: 2px solid #eee;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #3498db;
        }
        .package-icon {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 15px;
        }
        .package-name {
            font-weight: 600;
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .package-price {
            font-size: 24px;
            font-weight: 700;
            color: #27ae60;
            margin-bottom: 15px;
        }
        .package-features {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        .package-features li {
            color: #666;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .packages-section { margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <h1 class="logo-text">{{ config('app.name') }}</h1>
            <p class="text-white">Connect to High-Speed Internet</p>
        </div>

        <div class="row">
            <div class="col-md-9 ">
                <!-- Tabs -->
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#voucher" data-toggle="tab">
                            <i class="fas fa-ticket-alt"></i> Voucher
                        </a>
                    </li>
                    <li>
                        <a href="#member" data-toggle="tab">
                            <i class="fas fa-user"></i> Member
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Voucher Tab -->
                    <div class="tab-pane active" id="voucher">
                        <h3 class="text-center" mb-4>
                             Activate Voucher
                        </h3>

                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                @if(session('debug'))
                                    <hr>
                                    <small><strong>Debug Info:</strong> {{ session('debug') }}</small>
                                @endif
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('voucher.activate') }}" id="voucher-form">
                            @csrf
                            <div class="form-group">
                                <label for="voucher-code">
                                    Voucher Code
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-ticket-alt"></i>
                                    </span>
                                    <input type="text" 
                                           id="voucher-code"
                                           class="form-control input-lg" 
                                           name="code" 
                                           placeholder="Enter your voucher code"
                                           required>
                                    <span class="input-group-btn">
                                        <a class="btn btn-default btn-lg" href="{{ url('/scan') }}" title="Scan QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-success btn-lg">
                                    Activate
                                </button>
                            </div>
                        </form>

                        @if(session('session_info'))
                            <div class="panel">
                                <div class="panel-heading">Active Session</div>
                                <div class="panel-body">
                                    <table class="table">
                                        <tr>
                                            <td><i class="fas fa-user"></i> Username</td>
                                            <td class="text-right">{{ session('session_info.username') }}</td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-clock"></i> Time Left</td>
                                            <td class="text-right">{{ session('session_info.time_left') }}</td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-database"></i> Data Left</td>
                                            <td class="text-right">{{ session('session_info.data_left') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Member Tab -->
                    <div class="tab-pane" id="member">
                        <h3 class="text-center mb-4">
                            Member Login
                        </h3>

                        <form method="POST" action="{{ route('member.login') }}">
                            @csrf
                            <div class="form-group">
                                <label for="username">
                                     Username
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           id="username"
                                           class="form-control input-lg" 
                                           name="username" 
                                           placeholder="Enter your username"
                                           required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password">
                                     Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           id="password"
                                           class="form-control input-lg" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required>
                                </div>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-success btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packages -->
        <div class="row packages-section">
            <div class="col-md-3">
                <div class="package-card">
                    <i class="fas fa-coffee package-icon"></i>
                    <h3 class="package-name">Basic</h3>
                    <div class="package-price">UGX 5,000</div>
                    <ul class="package-features">
                        <li><i class="fas fa-check"></i> 1 Hour</li>
                        <li><i class="fas fa-check"></i> 1Mbps Speed</li>
                        <li><i class="fas fa-check"></i> 500MB Data</li>
                    </ul>
                    <button class="btn btn-success btn-block">Buy Now</button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="package-card">
                    <i class="fas fa-rocket package-icon"></i>
                    <h3 class="package-name">Standard</h3>
                    <div class="package-price">UGX 10,000</div>
                    <ul class="package-features">
                        <li><i class="fas fa-check"></i> 5 Hours</li>
                        <li><i class="fas fa-check"></i> 2Mbps Speed</li>
                        <li><i class="fas fa-check"></i> 2GB Data</li>
                    </ul>
                    <button class="btn btn-success btn-block">Buy Now</button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="package-card">
                    <i class="fas fa-star package-icon"></i>
                    <h3 class="package-name">Premium</h3>
                    <div class="package-price">UGX 20,000</div>
                    <ul class="package-features">
                        <li><i class="fas fa-check"></i> 12 Hours</li>
                        <li><i class="fas fa-check"></i> 5Mbps Speed</li>
                        <li><i class="fas fa-check"></i> 5GB Data</li>
                    </ul>
                    <button class="btn btn-success btn-block">Buy Now</button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="package-card">
                    <i class="fas fa-crown package-icon"></i>
                    <h3 class="package-name">Ultimate</h3>
                    <div class="package-price">UGX 50,000</div>
                    <ul class="package-features">
                        <li><i class="fas fa-check"></i> 24 Hours</li>
                        <li><i class="fas fa-check"></i> 10Mbps Speed</li>
                        <li><i class="fas fa-check"></i> Unlimited Data</li>
                    </ul>
                    <button class="btn btn-success btn-block">Buy Now</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
