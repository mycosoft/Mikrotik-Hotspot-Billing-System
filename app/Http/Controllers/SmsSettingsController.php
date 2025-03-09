<?php

namespace App\Http\Controllers;

use App\Models\SmsSettings;
use Illuminate\Http\Request;
use App\Services\SmsService;

class SmsSettingsController extends Controller
{
    public function index()
    {
        $settings = SmsSettings::first();
        return view('settings.sms', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:egosms,whatsapp',
            'api_key' => 'required',
            'api_secret' => 'required',
            'sender_id' => 'required',
        ]);

        $settings = SmsSettings::firstOrNew();
        $settings->fill($request->all());
        $settings->save();

        return redirect()
            ->back()
            ->with('success', 'SMS settings updated successfully');
    }

    public function testSms(Request $request)
    {
        $request->validate([
            'test_phone' => 'required',
            'test_message' => 'required'
        ]);

        try {
            $smsService = new SmsService();
            $smsService->sendSms($request->test_phone, $request->test_message);

            return response()->json(['message' => 'Test message sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 