<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HotspotAuthController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function authenticate(Request $request)
    {
        Log::info('Hotspot authentication attempt', $request->all());

        // Validate the request
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'mac' => 'required|string',
            'ip' => 'required|string',
            'router' => 'required|exists:routers,id'
        ]);

        try {
            // Find the voucher
            $voucher = Voucher::where('code', $request->username)
                ->where('is_used', false)
                ->where('routers', $request->router)
                ->first();

            if (!$voucher) {
                Log::warning('Invalid voucher attempt', [
                    'code' => $request->username,
                    'mac' => $request->mac,
                    'ip' => $request->ip
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid voucher code'
                ]);
            }

            // Check if voucher is expired
            if ($voucher->expires_at && $voucher->expires_at < now()) {
                Log::warning('Expired voucher attempt', [
                    'code' => $request->username,
                    'expired_at' => $voucher->expires_at
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher has expired'
                ]);
            }

            // Get the router
            $router = Router::find($request->router);
            if (!$router || !$router->is_active) {
                Log::error('Router not found or inactive', [
                    'router_id' => $request->router
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Router not available'
                ]);
            }

            // Add user to MikroTik hotspot
            $result = $this->mikrotikService->addHotspotUser(
                $router,
                $voucher->code,
                $voucher->code,
                $voucher->plan,
                $request->mac,
                $request->ip
            );

            if (!$result['success']) {
                Log::error('Failed to add hotspot user', [
                    'error' => $result['message'],
                    'voucher' => $voucher->code
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to activate voucher'
                ]);
            }

            // Mark voucher as used
            $voucher->update([
                'is_used' => true,
                'used_at' => now(),
                'mac_address' => $request->mac,
                'ip_address' => $request->ip
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Voucher activated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Hotspot authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request'
            ]);
        }
    }

    public function status(Request $request)
    {
        try {
            $mac = $request->input('mac');
            $ip = $request->input('ip');
            $router = Router::find($request->input('router'));

            if (!$router) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router not found'
                ]);
            }

            $activeUser = $this->mikrotikService->getActiveHotspotUser($router, $mac);

            return response()->json([
                'success' => true,
                'active' => !empty($activeUser),
                'user' => $activeUser
            ]);

        } catch (\Exception $e) {
            Log::error('Hotspot status check error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check status'
            ]);
        }
    }

    /**
     * Activate a voucher code
     */
    public function activateVoucher(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'voucher' => 'required|string',
                'mac' => 'required|string',
                'ip' => 'required|string'
            ]);

            // Find the voucher
            $voucher = Voucher::where('code', $validated['voucher'])
                ->where('status', 'unused')
                ->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or used voucher code'
                ]);
            }

            // Get the router
            $router = Router::first(); // You might want to get the specific router based on your setup

            if (!$router) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router not configured'
                ]);
            }

            // Get the plan
            $plan = $voucher->internetPlan;

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid plan configuration'
                ]);
            }

            // Add user to MikroTik hotspot
            $mikrotikService = app(MikrotikService::class);
            $result = $mikrotikService->addHotspotUser(
                $router,
                $validated['voucher'],  // Use voucher code as username
                $validated['voucher'],  // Use voucher code as password
                $plan,
                $validated['mac'],
                $validated['ip']
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

            // Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'mac_address' => $validated['mac'],
                'ip_address' => $validated['ip'],
                'used_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Voucher activated successfully',
                'username' => $validated['voucher'],
                'password' => $validated['voucher']
            ]);

        } catch (\Exception $e) {
            \Log::error('Voucher activation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate voucher: ' . $e->getMessage()
            ]);
        }
    }
}
