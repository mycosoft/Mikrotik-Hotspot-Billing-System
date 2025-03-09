<?php

namespace App\Http\Controllers;

use App\Models\Bandwidth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BandwidthController extends Controller
{
    /**
     * Display a list of bandwidths
     */
    public function index(Request $request)
    {
        $query = Bandwidth::query();

        // Filtering
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1');
        }

        $bandwidths = $query->latest()->paginate(10);

        return view('bandwidths.index', compact('bandwidths'));
    }

    /**
     * Show the form for creating a new bandwidth
     */
    public function create()
    {
        return view('bandwidths.create');
    }

    /**
     * Store a newly created bandwidth
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:bandwidths,name',
            'rate_up' => 'required|numeric|min:0',
            'rate_up_unit' => 'required|in:Kbps,Mbps',
            'rate_down' => 'required|numeric|min:0',
            'rate_down_unit' => 'required|in:Kbps,Mbps',
            'burst_limit' => ['nullable', 'regex:/^\d+[MKmk]\/\d+[MKmk]$/'],
            'burst_threshold' => ['nullable', 'regex:/^\d+[MKmk]\/\d+[MKmk]$/'],
            'burst_time' => ['nullable', 'regex:/^\d+\/\d+$/'],
            'is_active' => 'boolean'
        ]);

        $bandwidth = Bandwidth::create([
            'name' => $validated['name'],
            'rate_up' => $validated['rate_up'],
            'rate_up_unit' => $validated['rate_up_unit'],
            'rate_down' => $validated['rate_down'],
            'rate_down_unit' => $validated['rate_down_unit'],
            'burst_limit' => $request->burst_limit,
            'burst_threshold' => $request->burst_threshold,
            'burst_time' => $request->burst_time,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('bandwidths.index')
            ->with('success', 'Bandwidth plan created successfully.');
    }

    /**
     * Display the specified bandwidth
     */
    public function show(Bandwidth $bandwidth)
    {
        $bandwidth->load('internetPlans');
        return view('bandwidths.show', compact('bandwidth'));
    }

    /**
     * Show the form for editing a bandwidth
     */
    public function edit(Bandwidth $bandwidth)
    {
        return view('bandwidths.edit', compact('bandwidth'));
    }

    /**
     * Update the specified bandwidth
     */
    public function update(Request $request, Bandwidth $bandwidth)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate_up' => 'required|numeric|min:0',
            'rate_up_unit' => 'required|in:Mbps,Kbps',
            'rate_down' => 'required|numeric|min:0',
            'rate_down_unit' => 'required|in:Mbps,Kbps',
            'is_active' => 'boolean'
        ]);

        // Ensure we keep the units as they are submitted
        $bandwidth->update([
            'name' => $validated['name'],
            'rate_up' => $validated['rate_up'],
            'rate_up_unit' => $validated['rate_up_unit'],
            'rate_down' => $validated['rate_down'],
            'rate_down_unit' => $validated['rate_down_unit'],
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('bandwidths.index')
            ->with('success', 'Bandwidth updated successfully.');
    }

    /**
     * Remove the specified bandwidth
     */
    public function destroy(Bandwidth $bandwidth)
    {
        try {
            // Check for associated plans
            if ($bandwidth->internetPlans()->exists()) {
                return redirect()->back()
                    ->with('error', "Cannot delete bandwidth '{$bandwidth->name}' because it has associated internet plans.");
            }

            // Delete the bandwidth
            $bandwidth->delete();

            return redirect()->route('bandwidths.index')
                ->with('success', "Bandwidth '{$bandwidth->name}' deleted successfully.");
        } catch (\Exception $e) {
            \Log::error('Failed to delete bandwidth: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete bandwidth. Please try again.');
        }
    }

    /**
     * Toggle the status of a bandwidth
     */
    public function toggleStatus(Bandwidth $bandwidth)
    {
        $bandwidth->is_active = !$bandwidth->is_active;
        $bandwidth->save();

        return redirect()->back()
            ->with('success', "Bandwidth '{$bandwidth->name}' status updated successfully.");
    }
}
