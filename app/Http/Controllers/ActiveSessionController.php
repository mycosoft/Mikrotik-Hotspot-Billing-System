<?php

namespace App\Http\Controllers;

use App\Models\ActiveSession;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActiveSessionController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ActiveSession::with(['customer', 'router']);

            return DataTables::of($query)
                ->addColumn('customer_name', function ($session) {
                    return $session->customer->name;
                })
                ->addColumn('router_name', function ($session) {
                    return $session->router->name;
                })
                ->addColumn('uptime', function ($session) {
                    return gmdate("H:i:s", $session->uptime_seconds);
                })
                ->addColumn('data_usage', function ($session) {
                    $total = $session->bytes_in + $session->bytes_out;
                    return $this->formatBytes($total);
                })
                ->addColumn('actions', function ($session) {
                    return view('sessions.actions', compact('session'));
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('sessions.index');
    }

    public function destroy(ActiveSession $session)
    {
        $session->delete();
        return response()->json(['message' => 'Session deleted successfully']);
    }

    public function disconnect(ActiveSession $session)
    {
        try {
            // Disconnect the user from Mikrotik
            $this->mikrotik->disconnectUser($session->router, $session->session_id);
            
            // Delete the session from our database
            $session->delete();

            return response()->json(['message' => 'User disconnected successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to disconnect user: ' . $e->getMessage()], 500);
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
