<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('setting');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email',
            'currency' => 'required|string|max:10',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'invoice_prefix' => 'required|string|max:10',
            'invoice_footer' => 'nullable|string',
            'theme' => 'required|string',
            'session_timeout' => 'required|integer|min:1|max:1440',
            'enable_registration' => 'boolean',
            'default_role' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('settings.index')
                ->withErrors($validator)
                ->withInput();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo.' . $file->getClientOriginalExtension();
            
            // Delete old logo if exists
            if (Storage::exists('public/logo/' . $filename)) {
                Storage::delete('public/logo/' . $filename);
            }
            
            // Store new logo
            $file->storeAs('public/logo', $filename);
            Setting::set('logo', 'storage/logo/' . $filename);
        }

        // Update all other settings
        foreach ($request->except(['_token', 'logo']) as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Settings updated successfully');
    }

    public function smtp()
    {
        $settings = Setting::all()->keyBy('setting');
        return view('settings.smtp', compact('settings'));
    }

    public function updateSmtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_encryption' => 'required|in:tls,ssl'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('settings.smtp')
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->except('_token') as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()
            ->route('settings.smtp')
            ->with('success', 'SMTP settings updated successfully');
    }

    public function testEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('settings.smtp')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Send test email logic here
            // Mail::to($request->test_email)->send(new TestEmail());
            
            return redirect()
                ->route('settings.smtp')
                ->with('success', 'Test email sent successfully');
        } catch (\Exception $e) {
            return redirect()
                ->route('settings.smtp')
                ->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
