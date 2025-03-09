<?php

namespace App\Http\Controllers;

use App\Models\Bandwidth;
use App\Models\InternetPlan;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InternetPlanController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Display a listing of internet plans
     */
    public function index(Request $request)
    {
        $query = InternetPlan::with(['bandwidth', 'router']);

        // Filter by search term
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by bandwidth
        if ($request->filled('bandwidth_id')) {
            $query->where('bandwidth_id', $request->bandwidth_id);
        }

        // Filter by router
        if ($request->filled('router_id')) {
            $query->where('router_id', $request->router_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $plans = $query->latest()->paginate(10);
        $bandwidths = Bandwidth::where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();

        return view('plans.index', compact('plans', 'bandwidths', 'routers'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        $bandwidths = Bandwidth::where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();
        return view('plans.create', compact('bandwidths', 'routers'));
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:limited,unlimited',
            'limit_type' => 'required_if:type,limited|in:time,data,both',
            'time_limit' => 'required_if:limit_type,time,both|nullable|integer|min:1',
            'data_limit' => 'required_if:limit_type,data,both|nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'bandwidth_id' => 'required|exists:bandwidths,id',
            'router_id' => 'required|exists:routers,id',
            'simultaneous_sessions' => 'required|integer|min:1',
        ]);

        try {
            // Format name - only alphanumeric
            $name = preg_replace('/[^a-zA-Z0-9]/', '', $request->name);

            $plan = InternetPlan::create([
                'name' => $name,
                'type' => $request->type,
                'limit_type' => $request->type === 'limited' ? $request->limit_type : null,
                'time_limit' => $request->time_limit,
                'data_limit' => $request->data_limit,
                'price' => $request->price,
                'validity_days' => $request->validity_days,
                'bandwidth_id' => $request->bandwidth_id,
                'router_id' => $request->router_id,
                'simultaneous_sessions' => $request->simultaneous_sessions,
            ]);

            // Sync plan to router
            app(MikrotikService::class)->syncPlan($plan);

            return redirect()
                ->route('plans.index')
                ->with('success', 'Plan created successfully.');

        } catch (\Exception $e) {
            \Log::error('Failed to create plan', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create plan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified plan
     */
    public function show(InternetPlan $plan)
    {
        $plan->load(['bandwidth', 'router']);
        
        // Get usage statistics
        $activeVouchers = $plan->vouchers()
            ->where('is_used', true)
            ->where('expires_at', '>', now())
            ->count();

        $totalVouchers = $plan->vouchers()->count();
        $revenue = $plan->vouchers()->sum('price');

        return view('plans.show', compact('plan', 'activeVouchers', 'totalVouchers', 'revenue'));
    }

    /**
     * Show the form for editing the plan
     */
    public function edit(InternetPlan $plan)
    {
        $plan->load(['bandwidth', 'router']);
        $bandwidths = Bandwidth::where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();
        return view('plans.edit', compact('plan', 'bandwidths', 'routers'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, InternetPlan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bandwidth_id' => 'required|exists:bandwidths,id',
            'router_id' => 'required|exists:routers,id',
            'simultaneous_sessions' => 'required|integer|min:1',
        ]);

        try {
            // Format name exactly like mycosoft-hotspot - only alphanumeric
            $name = preg_replace('/[^a-zA-Z0-9]/', '', $request->name);

            $plan->update([
                'name' => $name,
                'bandwidth_id' => $request->bandwidth_id,
                'router_id' => $request->router_id,
                'simultaneous_sessions' => $request->simultaneous_sessions,
            ]);

            // Sync updated plan to router
            app(MikrotikService::class)->syncPlan($plan);

            return redirect()
                ->route('plans.index')
                ->with('success', 'Plan updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Failed to update plan', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update plan: ' . $e->getMessage());
        }
    }

    /**
     * Sync all plans or a specific plan to router
     */
    public function sync(Request $request)
    {
        try {
            if ($request->has('plan_id')) {
                $plan = InternetPlan::findOrFail($request->plan_id);
                
                try {
                    $this->mikrotikService->syncPlan($plan);
                    
                    return response()->json([
                        'success' => true,
                        'title' => 'Success!',
                        'message' => "Plan '{$plan->name}' synced successfully",
                        'icon' => 'success'
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'title' => 'Error!',
                        'message' => 'Sync failed: ' . $e->getMessage(),
                        'icon' => 'error'
                    ], 500);
                }
            } else {
                // Sync all enabled plans
                $plans = InternetPlan::where('is_active', true)->get();
                $success = 0;
                $failed = 0;

                foreach ($plans as $plan) {
                    try {
                        if ($plan->router && $plan->router->is_active) {
                            $this->mikrotikService->syncPlan($plan);
                            $success++;
                        } else {
                            $failed++;
                        }
                    } catch (\Exception $e) {
                        Log::error('Plan sync failed', [
                            'plan_id' => $plan->id,
                            'error' => $e->getMessage()
                        ]);
                        $failed++;
                    }
                }

                return response()->json([
                    'success' => true,
                    'title' => 'Sync Complete',
                    'message' => "Success: $success, Failed: $failed",
                    'icon' => $failed > 0 ? 'warning' : 'success'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Plan sync failed', [
                'error' => $e->getMessage(),
                'plan_id' => $request->plan_id ?? 'all'
            ]);
            
            return response()->json([
                'success' => false,
                'title' => 'Error!',
                'message' => 'Failed to sync plan(s): ' . $e->getMessage(),
                'icon' => 'error'
            ], 500);
        }
    }

    /**
     * Remove the specified plan
     */
    public function destroy(InternetPlan $plan)
    {
        try {
            // Check if plan has any active vouchers
            if ($plan->vouchers()->where('is_used', true)->exists()) {
                return response()->json([
                    'message' => 'Cannot delete plan with active vouchers.'
                ], 422);
            }

            // Remove plan from router if enabled
            if ($plan->is_active && $plan->router && $plan->router->is_active) {
                $this->mikrotikService->removePlan($plan);
            }

            $plan->delete();

            return response()->json([
                'message' => "Plan '{$plan->name}' deleted successfully."
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete plan', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to delete plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus(InternetPlan $plan)
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return back()->with('success', 'Plan status updated successfully.');
    }

    /**
     * Clone an existing plan
     */
    public function clone(InternetPlan $plan)
    {
        $newPlan = $plan->replicate();
        $newPlan->name = $plan->name . ' (Copy)';
        $newPlan->save();

        return redirect()->route('plans.edit', $newPlan)
            ->with('success', 'Plan cloned successfully. You can now modify the copy.');
    }
}
