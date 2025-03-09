<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Customer;
use App\Models\ActiveSession;
use App\Models\Router;
use App\Services\MikrotikService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VoucherActivationController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function showActivationForm(Request $request)
    {
        return view('public.activate', [
            'code' => $request->code
        ]);
    }

    public function activate(Request $request)
    {
        Log::info('Voucher activation request', $request->all());

        // Validate the request
        $request->validate([
            'voucher' => 'required|string',
            'mac' => 'required|string',
            'ip' => 'required|string',
            'hostname' => 'required|string'
        ]);

        // Clean up the voucher code
        $code = str_replace('-', '', strtoupper($request->voucher));

        try {
            return DB::transaction(function () use ($request, $code) {
                // Find and lock the voucher
                $voucher = Voucher::with(['plan.bandwidth', 'router'])
                    ->whereRaw("BINARY code = ?", [$code])
                    ->where('is_used', false)
                    ->lockForUpdate()
                    ->first();

                if (!$voucher) {
                    Log::warning('Invalid voucher attempt', ['code' => $code]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid voucher code or voucher already used.'
                    ]);
                }

                if ($voucher->expires_at && $voucher->expires_at < now()) {
                    Log::warning('Expired voucher attempt', [
                        'code' => $code,
                        'expired_at' => $voucher->expires_at
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'This voucher has expired.'
                    ]);
                }

                // Get the router
                $router = $voucher->router;

                if (!$router) {
                    Log::warning('Router not found', [
                        'voucher_id' => $voucher->id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Router not found. Please contact support.'
                    ]);
                }

                if (!$router->is_active) {
                    Log::warning('Router is inactive', [
                        'router_id' => $router->id,
                        'router_name' => $router->name
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Router is currently inactive. Please contact support.'
                    ]);
                }

                // Test router connection before proceeding
                try {
                    $this->mikrotik->connect($router);
                } catch (\Exception $e) {
                    Log::error('Router connection failed', [
                        'router_id' => $router->id,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to connect to router. Please try again later.'
                    ]);
                }

                // Create or update customer
                $customer = Customer::firstOrCreate(
                    ['mac_address' => $request->mac],
                    [
                        'name' => 'Guest-' . Str::random(6),
                        'email' => null,
                        'phone' => null,
                        'status' => 'active'
                    ]
                );

                // Add user to MikroTik hotspot
                try {
                    $result = $this->mikrotik->addHotspotUser(
                        $router,
                        $code, // username
                        $code, // password
                        $voucher->plan,
                        $request->mac,
                        $request->ip
                    );

                    if (!$result['success']) {
                        throw new \Exception($result['message']);
                    }

                    // Mark voucher as used
                    $voucher->update([
                        'is_used' => true,
                        'used_at' => now(),
                        'mac_address' => $request->mac,
                        'customer_id' => $customer->id
                    ]);

                    // Create active session
                    ActiveSession::create([
                        'customer_id' => $customer->id,
                        'router_id' => $router->id,
                        'voucher_id' => $voucher->id,
                        'mac_address' => $request->mac,
                        'ip_address' => $request->ip,
                        'started_at' => now(),
                        'expires_at' => $voucher->expires_at
                    ]);

                    Log::info('Voucher activated successfully', [
                        'voucher_code' => $code,
                        'customer_id' => $customer->id,
                        'mac' => $request->mac
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Voucher activated successfully',
                        'username' => $code,
                        'password' => $code
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to add hotspot user', [
                        'error' => $e->getMessage(),
                        'voucher' => $code
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to activate voucher: ' . $e->getMessage()
                    ]);
                }
            });

        } catch (\Exception $e) {
            Log::error('Voucher activation failed', [
                'error' => $e->getMessage(),
                'code' => $code ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    public function getClientInfo(Request $request)
    {
        return response()->json([
            'mac_address' => $request->header('X-Client-MAC') ?? null,
            'ip_address' => $request->ip()
        ]);
    }
}
