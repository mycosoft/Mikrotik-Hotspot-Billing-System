<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Voucher;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerController extends BaseController
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by service type
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get customers with their active sessions
        $customers = $query->withCount('activeSessions')
                         ->latest()
                         ->paginate(10)
                         ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:customers',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:255|unique:customers',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'service_type' => 'required|in:PPPoE,Hotspot,Static',
            'status' => 'required|in:Active,Inactive,Suspended',
            'pppoe_username' => 'required_if:service_type,PPPoE|nullable|string|max:255',
            'pppoe_password' => 'required_if:service_type,PPPoE|nullable|string|min:6',
            'ip_address' => 'nullable|ip',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('customers.create')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $customer = new Customer();
            $customer->name = $request->name;
            $customer->username = $request->username;
            $customer->password = Hash::make($request->password);
            $customer->email = $request->email;
            $customer->phone = $request->phone;
            $customer->address = $request->address;
            $customer->service_type = $request->service_type;
            $customer->status = $request->status;
            $customer->pppoe_username = $request->pppoe_username;
            $customer->pppoe_password = $request->pppoe_password;
            $customer->ip_address = $request->ip_address;
            $customer->latitude = $request->latitude;
            $customer->longitude = $request->longitude;
            
            // If service type is PPPoE, create PPPoE user in Mikrotik
            if ($request->service_type === 'PPPoE' && !empty($request->pppoe_username)) {
                $this->mikrotikService->createPPPoEUser([
                    'name' => $request->pppoe_username,
                    'password' => $request->pppoe_password,
                    'service' => 'pppoe',
                    'profile' => 'default', // You might want to make this configurable
                    'remote-address' => $request->ip_address,
                ]);
            }

            $customer->save();

            return redirect()
                ->route('customers.show', $customer)
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            // If PPPoE user was created but customer save failed, clean up
            if ($request->service_type === 'PPPoE' && !empty($request->pppoe_username)) {
                try {
                    $this->mikrotikService->removePPPoEUser($request->pppoe_username);
                } catch (\Exception $cleanup) {
                    // Log cleanup failure but don't throw
                    \Log::error('Failed to clean up PPPoE user after customer creation failure', [
                        'username' => $request->pppoe_username,
                        'error' => $cleanup->getMessage()
                    ]);
                }
            }

            \Log::error('Failed to create customer', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password', 'pppoe_password'])
            ]);

            return redirect()
                ->route('customers.create')
                ->with('error', 'Failed to create customer. Please try again.')
                ->withInput($request->except(['password', 'pppoe_password']));
        }
    }

    /**
     * Display the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        // Load the relationships we need
        $customer->load([
            'transactions' => function ($query) {
                $query->latest()->take(10);
            },
            'vouchers' => function ($query) {
                $query->with('plan')->latest()->take(10);
            }
        ]);

        // Get active sessions separately since we want the latest ones
        $activeSessions = $customer->activeSessions()
            ->latest()
            ->take(10)
            ->get();

        return view('customers.show', compact('customer', 'activeSessions'));
    }

    /**
     * Show the form for editing the customer
     */
    public function edit(Customer $customer)
    {
        // Get unused vouchers
        $vouchers = Voucher::where('is_used', false)
            ->with('plan')
            ->latest()
            ->get();

        return view('customers.edit', compact('customer', 'vouchers'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:customers,username,' . $customer->id,
            'password' => 'nullable|string|min:6',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'service_type' => 'required|in:PPPoE,Hotspot,Static',
            'status' => 'required|in:Active,Inactive,Suspended',
            'pppoe_username' => 'required_if:service_type,PPPoE|nullable|string|max:255',
            'pppoe_password' => 'required_if:service_type,PPPoE|nullable|string|min:6',
            'ip_address' => 'nullable|ip',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->handleValidationError($validator);
        }

        $input = $request->all();
        
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        $customer->update($input);

        if ($request->wantsJson()) {
            return $this->sendResponse($customer, 'Customer updated successfully.');
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        // Remove hotspot user from Mikrotik
        $this->mikrotikService->removeHotspotUser($customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Toggle customer status
     */
    public function toggleStatus(Customer $customer)
    {
        $customer->is_active = !$customer->is_active;
        $customer->save();

        if ($customer->is_active) {
            $this->mikrotikService->addHotspotUser($customer);
        } else {
            $this->mikrotikService->removeHotspotUser($customer);
        }

        return back()->with('success', 'Customer status updated successfully.');
    }

    /**
     * Show customer's active sessions
     */
    public function sessions(Customer $customer)
    {
        $sessions = $customer->activeSessions()
            ->with('router')
            ->latest()
            ->paginate(10);

        return view('customers.sessions', compact('customer', 'sessions'));
    }

    /**
     * Show customer's voucher history
     */
    public function vouchers(Customer $customer)
    {
        $vouchers = $customer->vouchers()
            ->with('plan')
            ->latest()
            ->paginate(10);

        return view('customers.vouchers', compact('customer', 'vouchers'));
    }

    /**
     * Export customers to CSV
     */
    public function export()
    {
        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Username',
                'Email',
                'Phone',
                'Address',
                'Service Type',
                'Status',
                'Balance',
                'PPPoE Username',
                'IP Address',
                'Latitude',
                'Longitude',
                'Created At'
            ]);

            // Add customer data
            Customer::chunk(100, function($customers) use ($file) {
                foreach ($customers as $customer) {
                    fputcsv($file, [
                        $customer->id,
                        $customer->name,
                        $customer->username,
                        $customer->email,
                        $customer->phone,
                        $customer->address,
                        $customer->service_type,
                        $customer->status,
                        $customer->balance,
                        $customer->pppoe_username,
                        $customer->ip_address,
                        $customer->latitude,
                        $customer->longitude,
                        $customer->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show customer recharge form
     */
    public function showRecharge(Customer $customer)
    {
        return view('customers.recharge', compact('customer'));
    }

    /**
     * Process customer recharge
     */
    public function recharge(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('customers.recharge', $customer)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function() use ($customer, $request) {
                // Update customer balance
                $customer->balance += $request->amount;
                $customer->save();

                // Create transaction record
                $customer->transactions()->create([
                    'type' => 'credit',
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'reference' => $request->reference,
                    'notes' => $request->notes
                ]);
            });

            return redirect()
                ->route('customers.show', $customer)
                ->with('success', 'Customer balance updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Failed to process customer recharge', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('customers.recharge', $customer)
                ->with('error', 'Failed to process recharge. Please try again.')
                ->withInput();
        }
    }

    /**
     * Assign a voucher to a customer
     */
    public function assignVoucher(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'voucher_id' => 'required|exists:vouchers,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $voucher = Voucher::findOrFail($request->voucher_id);

        // Check if voucher is already used
        if ($voucher->is_used) {
            return redirect()
                ->back()
                ->with('error', 'This voucher has already been used.');
        }

        // Deactivate current active voucher if exists
        if ($customer->activeVoucher) {
            $customer->activeVoucher->update([
                'is_used' => false,
                'used_at' => null,
                'used_by' => null,
                'expires_at' => null,
                'customer_id' => null
            ]);
        }

        // Assign new voucher
        $voucher->update([
            'is_used' => true,
            'used_at' => now(),
            'used_by' => auth()->id(),
            'expires_at' => now()->addDays($voucher->validity_days),
            'customer_id' => $customer->id
        ]);

        return redirect()
            ->back()
            ->with('success', 'Voucher assigned successfully.');
    }
}
