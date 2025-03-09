<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily()
    {
        $transactions = Transaction::whereDate('created_at', Carbon::today())
            ->with(['customer', 'plan'])
            ->latest()
            ->get();

        $totalAmount = $transactions->sum('amount');

        return view('reports.daily', compact('transactions', 'totalAmount'));
    }

    public function monthly()
    {
        $transactions = Transaction::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->with(['customer', 'plan'])
            ->latest()
            ->get();

        $totalAmount = $transactions->sum('amount');

        return view('reports.monthly', compact('transactions', 'totalAmount'));
    }

    public function dateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $transactions = Transaction::whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->with(['customer', 'plan'])
            ->latest()
            ->get();

        $totalAmount = $transactions->sum('amount');

        return view('reports.date-range', compact('transactions', 'totalAmount'));
    }
}
