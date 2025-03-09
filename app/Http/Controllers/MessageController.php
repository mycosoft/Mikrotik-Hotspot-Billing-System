<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function single()
    {
        try {
            $customers = Customer::select('id', 'name', 'phone')->get();
            return view('messages.single', compact('customers'));
        } catch (\Exception $e) {
            Log::error('Error in single message view: ' . $e->getMessage());
            return back()->with('error', 'Unable to load customers. Please try again.');
        }
    }

    public function bulk()
    {
        try {
            $customers = Customer::select('id', 'name', 'phone')->get();
            return view('messages.bulk', compact('customers'));
        } catch (\Exception $e) {
            Log::error('Error in bulk message view: ' . $e->getMessage());
            return back()->with('error', 'Unable to load customers. Please try again.');
        }
    }

    public function sendSingle(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'message' => 'required|string|max:1000',
                'message_type' => 'required|in:sms,whatsapp',
            ]);

            $customer = Customer::findOrFail($request->customer_id);
            
            if ($request->message_type === 'whatsapp') {
                $this->smsService->sendWhatsApp($customer->phone, $request->message);
            } else {
                $this->smsService->sendSms($customer->phone, $request->message);
            }

            // Log the message
            DB::table('sms_logs')->insert([
                'customer_id' => $customer->id,
                'phone' => $customer->phone,
                'message' => $request->message,
                'type' => $request->message_type,
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Message sent successfully']);
        } catch (\Exception $e) {
            Log::error('Error sending single message: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }

    public function sendBulk(Request $request)
    {
        try {
            $request->validate([
                'customer_ids' => 'required|array',
                'customer_ids.*' => 'exists:customers,id',
                'message' => 'required|string|max:1000',
                'message_type' => 'required|in:sms,whatsapp',
            ]);

            $successCount = 0;
            $failCount = 0;

            foreach ($request->customer_ids as $customerId) {
                try {
                    $customer = Customer::findOrFail($customerId);
                    
                    if ($request->message_type === 'whatsapp') {
                        $this->smsService->sendWhatsApp($customer->phone, $request->message);
                    } else {
                        $this->smsService->sendSms($customer->phone, $request->message);
                    }

                    // Log the message
                    DB::table('sms_logs')->insert([
                        'customer_id' => $customer->id,
                        'phone' => $customer->phone,
                        'message' => $request->message,
                        'type' => $request->message_type,
                        'status' => 'sent',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to send message to customer {$customerId}: " . $e->getMessage());
                    $failCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully sent {$successCount} messages. Failed: {$failCount}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk message sending: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
} 