<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Voucher;
use App\Models\InternetPlan;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    /**
     * Display a list of vouchers with filtering
     */
    public function index(Request $request)
    {
        $query = Voucher::with(['plan', 'generatedBy']);

        // Filtering
        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('router')) {
            $query->where('routers', $request->router);
        }

        if ($request->filled('plan')) {
            $query->where('plan_id', $request->plan);
        }

        if ($request->filled('status')) {
            $query->where('is_used', $request->status == '1');
        }

        // Get related data for filtering dropdowns
        $routers = Router::where('status', 'online')
            ->whereNull('deleted_at')
            ->pluck('name')
            ->toArray();
        $plans = InternetPlan::where('is_active', true)->get();
        $customers = Customer::exists() ? Customer::pluck('username')->toArray() : [];

        // Get all vouchers instead of paginating
        $vouchers = $query->latest()->get();

        return view('vouchers.index', compact('vouchers', 'routers', 'plans', 'customers'));
    }

    /**
     * Show the form for creating vouchers
     */
    public function create()
    {
        // Get all routers, including offline ones, but exclude deleted ones
        $routers = Router::whereNull('deleted_at')
                         ->orderBy('name')
                         ->get(['id', 'name', 'ip', 'is_online']);

        // Get active internet plans
        $plans = InternetPlan::where('is_active', true)
                            ->orderBy('name')
                            ->get();
        
        return view('vouchers.create', compact('routers', 'plans'));
    }

    /**
     * Generate vouchers
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:Hotspot,PPPOE',
            'router' => 'required|exists:routers,id',
            'plan_id' => 'required|exists:internet_plans,id',
            'number_of_vouchers' => 'required|integer|min:1|max:100',
            'voucher_format' => 'required|in:numbers,up,low,rand',
            'prefix' => 'nullable|string|max:10',
            'length_code' => 'required|integer|min:6|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vouchers = [];
        $plan = InternetPlan::findOrFail($request->plan_id);
        $router = Router::findOrFail($request->router);

        for ($i = 0; $i < $request->number_of_vouchers; $i++) {
            // Generate unique voucher code
            do {
                $code = $this->generateVoucherCode(
                    $request->voucher_format, 
                    $request->prefix, 
                    $request->length_code
                );
            } while (Voucher::where('code', $code)->exists());

            $vouchers[] = [
                'type' => $request->type,
                'routers' => $router->id,
                'plan_id' => $plan->id,
                'code' => $code,
                'status' => false,
                'generated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Voucher::insert($vouchers);

        return redirect()->route('vouchers.index')
            ->with('success', count($vouchers) . ' vouchers generated successfully');
    }

    /**
     * Generate voucher code based on selected format
     */
    private function generateVoucherCode($format, $prefix = '', $length = 12)
    {
        $prefix = $prefix ?: '';
        $baseLength = $length - strlen($prefix);

        switch ($format) {
            case 'numbers':
                $characters = '0123456789';
                $code = $prefix . substr(str_shuffle(str_repeat($characters, ceil($baseLength/strlen($characters)))), 0, $baseLength);
                break;
            case 'up':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = $prefix . substr(str_shuffle(str_repeat($characters, ceil($baseLength/strlen($characters)))), 0, $baseLength);
                break;
            case 'low':
                $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                $code = $prefix . substr(str_shuffle(str_repeat($characters, ceil($baseLength/strlen($characters)))), 0, $baseLength);
                break;
            case 'rand':
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = $prefix . substr(str_shuffle(str_repeat($characters, ceil($baseLength/strlen($characters)))), 0, $baseLength);
                break;
            default:
                $code = $prefix . Str::random($baseLength);
        }

        return $code;
    }

    /**
     * Print vouchers
     */
    public function print(Request $request)
    {
        $query = Voucher::query()
            ->with(['plan'])
            ->orderBy('id', 'desc');

        // Apply filters if provided
        if ($request->filled('from_id')) {
            $query->where('id', '>=', $request->from_id);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('router')) {
            $query->where('routers', $request->router);
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        $vouchers = $query->get();
        $plans = InternetPlan::where('is_active', true)->get();
        $routers = Router::orderBy('name')->get();

        return view('vouchers.print', [
            'vouchers' => $vouchers,
            'plans' => $plans,
            'routers' => $routers,
            'perLine' => $request->get('per_line', 3),
            'pageBreak' => $request->get('page_break', 12)
        ]);
    }

    /**
     * View a specific voucher
     */
    public function show(Voucher $voucher)
    {
        $voucher->load(['plan', 'customer']);
        return view('vouchers.show', compact('voucher'));
    }

    /**
     * Delete vouchers older than 3 months
     */
    public function deleteOldVouchers()
    {
        try {
            $threeMonthsAgo = now()->subMonths(3);
            // Only delete unused vouchers that are older than 3 months
            $count = Voucher::where('created_at', '<', $threeMonthsAgo)
                           ->where('is_used', false)
                           ->delete();
            
            return redirect()->route('vouchers.index')
                ->with('success', $count . ' old unused vouchers have been deleted.');
        } catch (\Exception $e) {
            return redirect()->route('vouchers.index')
                ->with('error', 'Failed to delete old vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Activate a voucher
     */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|exists:vouchers,code',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $voucher = Voucher::where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (!$voucher) {
            return redirect()->back()
                ->with('error', 'Invalid or already used voucher');
        }

        // Logic for voucher activation (similar to your existing implementation)
        // This might involve creating a transaction, updating user's plan, etc.

        $voucher->update([
            'is_used' => true,
            'used_at' => now(),
            'customer_id' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', 'Voucher activated successfully');
    }

    /**
     * Delete a specific voucher
     */
    public function destroy(Voucher $voucher)
    {
        // Prevent deletion of used vouchers
        if ($voucher->is_used) {
            return redirect()->route('vouchers.index')
                ->with('error', 'Cannot delete a used voucher.');
        }

        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher deleted successfully.');
    }

    public function printPreview(Voucher $voucher)
    {
        return view('vouchers.print-preview', compact('voucher'));
    }

    public function printMultiple(Request $request)
    {
        $vouchers = Voucher::whereIn('id', $request->voucher_ids)->get();
        return view('vouchers.print-multiple', compact('vouchers'));
    }
}
