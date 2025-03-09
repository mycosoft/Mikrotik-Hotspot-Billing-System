<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\InternetPlanController;
use App\Http\Controllers\BandwidthProfileController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActiveSessionController;
use App\Http\Controllers\BandwidthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\VoucherActivationController;
use App\Http\Controllers\ClientInfoController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\HotspotAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public Routes
Route::get('/', function () {
    return redirect()->route('voucher.activate');
});

Route::get('/activate', [VoucherActivationController::class, 'showActivationForm'])->name('voucher.activate');
Route::post('/activate', [VoucherActivationController::class, 'activate'])->name('voucher.activate.post');
Route::get('/api/client-info', [ClientInfoController::class, 'getInfo'])->name('api.client-info');

// Member Routes
Route::get('/member/login', [MemberController::class, 'showLoginForm'])->name('member.login');
Route::post('/member/login', [MemberController::class, 'login'])->name('member.login.post');
Route::get('/member/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard')->middleware('auth');
Route::get('/member/logout', [MemberController::class, 'logout'])->name('member.logout')->middleware('auth');

// Auth Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');

    // Customer routes
    Route::middleware('can:view customers')->group(function () {
        Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/toggle', [CustomerController::class, 'toggleStatus'])->name('customers.toggle');
        Route::get('customers/{customer}/sessions', [CustomerController::class, 'sessions'])->name('customers.sessions');
        Route::get('customers/{customer}/vouchers', [CustomerController::class, 'vouchers'])->name('customers.vouchers');
        Route::get('customers/{customer}/recharge', [CustomerController::class, 'showRecharge'])->name('customers.recharge');
        Route::post('customers/{customer}/recharge', [CustomerController::class, 'recharge'])->name('customers.recharge.store');
        Route::post('customers/{customer}/assign-voucher', [CustomerController::class, 'assignVoucher'])->name('customers.assign-voucher');
    });

    // Voucher routes
    Route::middleware('can:view vouchers')->group(function () {
        // Place print routes before resource route to avoid conflicts
        Route::get('vouchers/print', [VoucherController::class, 'print'])
            ->name('vouchers.print');
            
        Route::get('vouchers/print-multiple', [VoucherController::class, 'printMultiple'])
            ->name('vouchers.print-multiple');
            
        Route::get('vouchers/{voucher}/print-preview', [VoucherController::class, 'printPreview'])
            ->name('vouchers.print-preview');

        Route::resource('vouchers', VoucherController::class);
        
        Route::post('vouchers/activate', [VoucherController::class, 'activate'])
            ->name('vouchers.activate');
        
        Route::delete('vouchers/old', [VoucherController::class, 'deleteOldVouchers'])
            ->name('vouchers.delete-old');
    });

    // Internet Plan routes
    Route::middleware('can:view plans')->group(function () {
        Route::resource('plans', InternetPlanController::class);
        Route::post('plans/{plan}/toggle', [InternetPlanController::class, 'toggleStatus'])->name('plans.toggle');
        Route::post('plans/{plan}/clone', [InternetPlanController::class, 'clone'])->name('plans.clone');
    });

    // Bandwidth Profile routes
    Route::middleware('can:view bandwidth profiles')->group(function () {
        Route::resource('bandwidth', BandwidthProfileController::class);
        Route::post('bandwidth/{profile}/toggle', [BandwidthProfileController::class, 'toggleStatus'])->name('bandwidth.toggle');
    });

    // Bandwidth routes
    Route::middleware('can:manage bandwidths')->group(function () {
        Route::resource('bandwidths', BandwidthController::class);
        Route::post('bandwidths/{bandwidth}/toggle', [BandwidthController::class, 'toggleStatus'])->name('bandwidths.toggle');
        Route::delete('/bandwidths/{bandwidth}', [BandwidthController::class, 'destroy'])->name('bandwidths.destroy');
    });

    // Router Management Routes
    Route::middleware('can:view routers')->group(function () {
        Route::resource('routers', RouterController::class);
        Route::post('routers/{router}/sync', [RouterController::class, 'sync'])->name('routers.sync');
        Route::post('routers/{router}/toggle-status', [RouterController::class, 'toggleStatus'])->name('routers.toggle-status');
        Route::get('routers/{router}/sessions', [RouterController::class, 'sessions'])->name('routers.sessions');
        Route::get('routers/maps', [RouterController::class, 'maps'])->name('routers.maps');
        Route::get('routers/{router}/system-resources', [RouterController::class, 'getSystemResources'])->name('routers.system-resources');
    });

    // Session routes
    Route::middleware('can:view sessions')->group(function () {
        Route::get('sessions', [ActiveSessionController::class, 'index'])->name('sessions.index');
        Route::delete('sessions/{session}', [ActiveSessionController::class, 'destroy'])->name('sessions.destroy');
        Route::post('sessions/{session}/disconnect', [ActiveSessionController::class, 'disconnect'])->name('sessions.disconnect');
    });

    // Message Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('messages/single', [MessageController::class, 'single'])->name('messages.single');
        Route::post('messages/single', [MessageController::class, 'sendSingle'])->name('messages.send-single');
        Route::get('messages/bulk', [MessageController::class, 'bulk'])->name('messages.bulk');
        Route::post('messages/bulk', [MessageController::class, 'sendBulk'])->name('messages.send-bulk');
    });

    // Settings Routes
    Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])
            ->middleware('role:Super Admin|Admin')
            ->name('index');
        Route::get('/general', [SettingController::class, 'index'])
            ->middleware('role:Super Admin|Admin')
            ->name('general');
        Route::post('/update', [SettingController::class, 'update'])
            ->middleware('role:Super Admin|Admin')
            ->name('update');
        Route::get('/smtp', [SettingController::class, 'smtp'])->name('smtp');
        Route::post('/smtp/update', [SettingController::class, 'updateSmtp'])->name('smtp.update');
        Route::post('/smtp/test', [SettingController::class, 'testEmail'])->name('smtp.test');
    });

    // Report Routes
    Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        Route::get('/date-range', [ReportController::class, 'dateRange'])->name('date-range');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('plans', InternetPlanController::class);
    Route::post('plans/sync', [InternetPlanController::class, 'sync'])->name('plans.sync');

    // Router routes
    Route::resource('routers', RouterController::class);
    Route::post('routers/{router}/test-connection', [RouterController::class, 'testConnection'])
        ->name('routers.test-connection');
    Route::get('routers/{router}/status', [RouterController::class, 'getStatus'])
        ->name('routers.status');
});

Route::get('/debug-roles', function() {
    dd(auth()->user()->roles->pluck('name'));
});

// Add this route for development only
Route::get('/debug/mikrotik-logs', function() {
    $logs = file_get_contents(storage_path('logs/mikrotik-debug.log'));
    return response($logs)->header('Content-Type', 'text/plain');
})->middleware('auth');

Route::post('/test/mikrotik-connection', function(Request $request) {
    try {
        $client = new \RouterOS\Client([
            'host' => $request->ip,
            'user' => $request->username,
            'pass' => $request->password,
            'port' => $request->port ?? 8728
        ]);

        $result = [
            'identity' => $client->query('/system/identity/print')->read(),
            'hotspot' => $client->query('/ip/hotspot/print')->read(),
            'profiles' => $client->query('/ip/hotspot/user/profile/print')->read(),
            'queues' => $client->query('/queue/simple/print')->read()
        ];

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

// MikroTik Hotspot Routes
Route::post('/hotspot/auth', [HotspotAuthController::class, 'authenticate'])->name('hotspot.auth');
Route::get('/hotspot/status', [HotspotAuthController::class, 'status'])->name('hotspot.status');
Route::post('/hotspot/activate', [HotspotAuthController::class, 'activateVoucher'])->name('hotspot.activate');

require __DIR__.'/auth.php';
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::get('/settings/general', [SettingController::class, 'index'])->name('settings.general');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');