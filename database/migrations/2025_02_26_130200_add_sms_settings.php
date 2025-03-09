<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add SMS settings
        $settings = [
            [
                'setting' => 'sms_api_key',
                'value' => '',
                'description' => 'API key for SMS gateway',
                'type' => 'text'
            ],
            [
                'setting' => 'sms_sender_id',
                'value' => '',
                'description' => 'Sender ID for SMS messages',
                'type' => 'text'
            ]
        ];

        // Insert settings
        foreach ($settings as $setting) {
            DB::table('settings')->insert($setting);
        }
    }

    public function down()
    {
        // Remove SMS settings
        DB::table('settings')
            ->whereIn('setting', ['sms_api_key', 'sms_sender_id'])
            ->delete();
    }
};
