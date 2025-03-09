<?php

namespace App\Http\Controllers;

use App\Http\Requests\RouterRequest;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Services\RouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class RouterController extends BaseController
{
    protected $mikrotikService;
    protected $routerService;

    public function __construct(MikrotikService $mikrotikService, RouterService $routerService)
    {
        $this->mikrotikService = $mikrotikService;
        $this->routerService = $routerService;
    }

    /**
     * Display a listing of routers
     */
    public function index(Request $request)
    {
        $query = Router::query();

        // Search by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $routers = $query->latest()->paginate(10);

        return view('routers.index', compact('routers'));
    }

    /**
     * Show router locations on map
     */
    public function maps(Request $request)
    {
        $query = Router::whereNotNull('coordinates')->where('coordinates', '!=', '');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $routers = $query->get();

        return view('routers.maps', compact('routers'));
    }

    /**
     * Show the form for creating a new router
     */
    public function create()
    {
        return view('routers.create');
    }

    /**
     * Store a newly created router in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:32',
            'ip' => 'required_if:is_active,1',
            'username' => 'required_if:is_active,1',
            'password' => 'required_if:is_active,1',
        ]);

        try {
            $router = new Router([
                'name' => $request->name,
                'ip' => $request->ip,
                'username' => $request->username,
                'password' => $request->password,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            if ($request->test_connection) {
                // Test connection before saving
                $service = new MikrotikService();
                $client = $service->connect($router);
            }

            $router->save();

            return redirect()
                ->route('routers.index')
                ->with('success', 'Router added successfully');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the router
     */
    public function edit(Router $router)
    {
        return view('routers.edit', compact('router'));
    }

    /**
     * Update the specified router in storage.
     */
    public function update(Request $request, Router $router)
    {
        $request->validate([
            'name' => 'required|string|max:32',
            'ip' => 'required_if:is_active,1',
            'username' => 'required_if:is_active,1'
        ]);

        try {
            $router->name = $request->name;
            $router->ip = $request->ip;
            $router->username = $request->username;
            if ($request->password) {
                $router->password = $request->password;
            }
            $router->description = $request->description;
            $router->is_active = $request->is_active;

            if ($request->test_connection) {
                // Test connection before saving
                $service = new MikrotikService();
                $client = $service->connect($router);
            }

            $router->save();

            return redirect()
                ->route('routers.index')
                ->with('success', 'Router updated successfully');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete the router
     */
    public function destroy(Router $router)
    {
        $router->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Router deleted successfully.']);
        }

        return redirect()->route('routers.index')
            ->with('success', 'Router deleted successfully.');
    }

    /**
     * Test connection to router
     */
    public function testConnection(Request $request)
    {
        try {
            // Create temporary router instance
            $router = new Router([
                'ip' => $request->ip,
                'username' => $request->username,
                'password' => $request->password === 'current' ? Router::find($request->id)->password : $request->password
            ]);

            // Test connection using MikrotikService
            $service = new MikrotikService();
            $client = $service->connect($router);

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get router system resources
     */
    public function getSystemResources(Router $router)
    {
        $result = $this->routerService->getSystemResources($router);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * View active sessions on the router
     */
    public function sessions(Router $router)
    {
        if (!$this->mikrotikService->connect($router)) {
            return back()->with('error', 'Could not connect to router.');
        }

        $sessions = $this->mikrotikService->getActiveSessions();
        
        // Update local database with session information
        foreach ($sessions as $sessionData) {
            $router->activeSessions()->updateOrCreate(
                ['session_id' => $sessionData['session_id']],
                $sessionData
            );
        }

        $activeSessions = $router->activeSessions()
            ->with('customer')
            ->latest()
            ->paginate(10);

        return view('routers.sessions', compact('router', 'activeSessions'));
    }

    /**
     * Disconnect a session
     */
    public function disconnectSession(Router $router, $sessionId)
    {
        $session = $router->activeSessions()->where('session_id', $sessionId)->firstOrFail();

        if ($this->mikrotikService->connect($router)) {
            if ($this->mikrotikService->disconnectSession($session)) {
                $session->delete();
                return back()->with('success', 'Session disconnected successfully.');
            }
        }

        return back()->with('error', 'Failed to disconnect session.');
    }

    /**
     * Toggle router status
     */
    public function toggleStatus(Router $router)
    {
        // Prevent toggling if router has active sessions
        if ($router->is_active && $router->activeSessions()->exists()) {
            return back()->with('error', 'Cannot deactivate router with active sessions.');
        }

        $router->update([
            'is_active' => !$router->is_active
        ]);

        return back()->with('success', 'Router status updated successfully.');
    }

    /**
     * Get router online status
     */
    public function getStatus(Router $router)
    {
        try {
            $service = new MikrotikService();
            $client = $service->connect($router);
            
            // Update last seen
            $router->last_seen = now();
            $router->is_online = true;
            $router->save();

            return response()->json([
                'is_online' => true,
                'last_seen' => $router->last_seen->diffForHumans()
            ]);

        } catch (\Exception $e) {
            // Update online status to false
            $router->is_online = false;
            $router->save();

            return response()->json([
                'is_online' => false,
                'last_seen' => $router->last_seen ? $router->last_seen->diffForHumans() : null
            ]);
        }
    }

    /**
     * Sync router configuration and status
     */
    public function sync(Router $router)
    {
        try {
            // Test connection first
            $client = $this->mikrotikService->connect($router);
            
            // Get system resources using RouterOS API
            $response = $client->query('/system/resource/print')->read();
            
            if (!empty($response)) {
                $resources = [
                    'uptime' => $response[0]['uptime'],
                    'cpu-load' => $response[0]['cpu-load'],
                    'free-memory' => $response[0]['free-memory'],
                    'total-memory' => $response[0]['total-memory'],
                    'version' => $response[0]['version'],
                    'board-name' => $response[0]['board-name'],
                ];
            }
            
            // Update router status
            $router->is_online = true;
            $router->last_seen = now();
            $router->save();

            return response()->json([
                'success' => true,
                'message' => 'Router synced successfully',
                'data' => $resources ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Router sync failed: ' . $e->getMessage());
            
            // Update router status to offline
            $router->is_online = false;
            $router->save();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync router: ' . $e->getMessage()
            ], 422);
        }
    }
}
