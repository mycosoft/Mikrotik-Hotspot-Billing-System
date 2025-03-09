<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InternetPlan;
use App\Models\Router;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get settings
        $currency = Setting::get('currency', 'UGX');
        $hideMonthlyRegistered = Setting::get('hide_mrc', 'no') === 'yes';
        $hideMonthlySales = Setting::get('hide_tms', 'no') === 'yes';
        $hideVoucherStock = Setting::get('hide_vs', 'no') === 'yes';
        $hideUserExpired = Setting::get('hide_uet', 'no') === 'yes';

        // Get today's income
        $todayIncome = Transaction::whereDate('created_at', Carbon::today())
            ->sum('amount');

        // Get monthly income
        $monthlyIncome = Transaction::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        // Get active and expired customers
        $activeCustomers = Customer::where('status', 'Active')->count();
        $expiredCustomers = Customer::where('status', 'Inactive')->count();
        $totalCustomers = Customer::count();

        // Get customer balance
        $totalCustomerBalance = Customer::sum('balance');

        // Get monthly registered customers for the past 12 months
        $monthlyRegistered = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Customer::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyRegistered[$date->format('M Y')] = $count;
        }

        // Get monthly sales for the past 12 months
        $monthlySales = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = Transaction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            $monthlySales[$date->format('M Y')] = $amount;
        }

        // Get voucher statistics
        $voucherStats = [];
        $plans = InternetPlan::all();
        foreach ($plans as $plan) {
            $voucherStats[] = [
                'name_plan' => $plan->name,
                'unused' => Voucher::where('plan_id', $plan->id)
                    ->where('status', 'unused')
                    ->count(),
                'used' => Voucher::where('plan_id', $plan->id)
                    ->where('status', 'used')
                    ->count()
            ];
        }

        $totalVoucherStats = [
            'unused' => Voucher::where('status', 'unused')->count(),
            'used' => Voucher::where('status', 'used')->count()
        ];

        // Get available vouchers
        $availableVouchers = Voucher::where('status', 'unused')->count();

        // Get users expiring today
        $expiringToday = Customer::whereDate('expiry_date', Carbon::today())
            ->with(['plan', 'router'])
            ->get();

        // Get offline routers
        $offlineRouters = Router::where(function ($query) {
                $query->where('status', 'offline')
                    ->orWhere('last_seen', '<', Carbon::now()->subMinutes(5));
            })
            ->where('is_active', true)
            ->get();

        // Count offline routers
        $offlineRoutersCount = $offlineRouters->count();

        return view('dashboard', compact(
            'currency',
            'hideMonthlyRegistered',
            'hideMonthlySales',
            'hideVoucherStock',
            'hideUserExpired',
            'todayIncome',
            'monthlyIncome',
            'activeCustomers',
            'expiredCustomers',
            'totalCustomers',
            'totalCustomerBalance',
            'monthlyRegistered',
            'monthlySales',
            'voucherStats',
            'totalVoucherStats',
            'expiringToday',
            'offlineRouters',
            'availableVouchers',
            'offlineRoutersCount'
        ));
    }

    public function refresh()
    {
        // Update router statuses
        Router::where('is_active', true)->each(function ($router) {
            $router->updateStatus();
        });

        return redirect()->route('dashboard.index')->with('success', 'Dashboard data refreshed');
    }
}
