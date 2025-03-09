<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\Customer;
use App\Models\ActiveSession;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function showLoginForm()
    {
        return view('public.member-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            // Get the active router
            $router = Router::where('status', 'online')->first();
            if (!$router) {
                return back()->with('error', 'No active router available');
            }

            // Get client's MAC address
            $clientMac = $this->mikrotik->getClientMacAddress($router, $request->ip());
            if (!$clientMac) {
                return back()->with('error', 'Could not detect your device. Please make sure you are connected to the hotspot.');
            }

            // Try to authenticate with Mikrotik
            $loginResult = $this->mikrotik->loginHotspotUser(
                $router,
                $request->username,
                $request->password,
                $clientMac,
                $request->ip()
            );

            if (!$loginResult['success']) {
                return back()->with('error', 'Invalid username or password');
            }

            // Get user info from Mikrotik
            $userInfo = $this->mikrotik->getHotspotUserInfo($router, $request->username);
            
            if (!$userInfo) {
                return back()->with('error', 'Could not retrieve user information');
            }

            // Create or update local customer record
            $customer = Customer::updateOrCreate(
                ['username' => $request->username],
                [
                    'mac_address' => $clientMac,
                    'is_active' => true,
                    'last_login' => now()
                ]
            );

            // Create or update active session
            $session = ActiveSession::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'mac_address' => $clientMac,
                ],
                [
                    'router_id' => $router->id,
                    'username' => $request->username,
                    'ip_address' => $request->ip(),
                    'started_at' => now(),
                    'expires_at' => $userInfo['expires_at'] ?? null
                ]
            );

            // Store session info
            $sessionInfo = [
                'username' => $request->username,
                'plan' => $userInfo['profile'] ?? 'Default',
                'time_left' => $userInfo['time_left'] ?? 'Unlimited',
                'data_left' => $userInfo['data_left'] ?? 'Unlimited',
                'uptime' => $userInfo['uptime'] ?? '0',
                'bytes_in' => $userInfo['bytes_in'] ?? '0',
                'bytes_out' => $userInfo['bytes_out'] ?? '0'
            ];

            return redirect()->route('member.dashboard')->with([
                'success' => 'Login successful! You now have internet access.',
                'session_info' => $sessionInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Member Login Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while logging in. Please try again.');
        }
    }

    public function dashboard()
    {
        try {
            $router = Router::where('status', 'online')->first();
            if (!$router) {
                return redirect()->route('member.login')->with('error', 'No active router available');
            }

            $sessionInfo = session('session_info');
            if (!$sessionInfo) {
                return redirect()->route('member.login');
            }

            // Get updated user info from Mikrotik
            $userInfo = $this->mikrotik->getHotspotUserInfo($router, $sessionInfo['username']);
            
            if ($userInfo) {
                $sessionInfo = [
                    'username' => $sessionInfo['username'],
                    'plan' => $userInfo['profile'] ?? 'Default',
                    'time_left' => $userInfo['time_left'] ?? 'Unlimited',
                    'data_left' => $userInfo['data_left'] ?? 'Unlimited',
                    'uptime' => $userInfo['uptime'] ?? '0',
                    'bytes_in' => $userInfo['bytes_in'] ?? '0',
                    'bytes_out' => $userInfo['bytes_out'] ?? '0'
                ];
            }

            return view('public.member-dashboard', [
                'sessionInfo' => $sessionInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            return redirect()->route('member.login')->with('error', 'An error occurred. Please login again.');
        }
    }

    public function logout(Request $request)
    {
        try {
            $router = Router::where('status', 'online')->first();
            if ($router) {
                $sessionInfo = session('session_info');
                if ($sessionInfo) {
                    // Logout from Mikrotik
                    $this->mikrotik->logoutHotspotUser(
                        $router,
                        $sessionInfo['username'],
                        $request->ip()
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage());
        }

        session()->forget('session_info');
        return redirect()->route('member.login')->with('success', 'You have been logged out successfully');
    }
}
