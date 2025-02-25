<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();
        });

        // Insert default settings
        $settings = [
            [
                'setting' => 'company_name',
                'value' => 'MST Wireless',
                'description' => 'Company name displayed in the application',
                'type' => 'text'
            ],
            [
                'setting' => 'company_address',
                'value' => '',
                'description' => 'Company address for invoices and reports',
                'type' => 'textarea'
            ],
            [
                'setting' => 'company_phone',
                'value' => '',
                'description' => 'Company phone number',
                'type' => 'text'
            ],
            [
                'setting' => 'company_email',
                'value' => '',
                'description' => 'Company email address',
                'type' => 'email'
            ],
            [
                'setting' => 'currency',
                'value' => 'UGX',
                'description' => 'Default currency for prices',
                'type' => 'text'
            ],
            [
                'setting' => 'tax_rate',
                'value' => '0',
                'description' => 'Default tax rate percentage',
                'type' => 'number'
            ],
            [
                'setting' => 'invoice_prefix',
                'value' => 'INV',
                'description' => 'Prefix for invoice numbers',
                'type' => 'text'
            ],
            [
                'setting' => 'invoice_footer',
                'value' => 'Thank you for your business!',
                'description' => 'Text to display at the bottom of invoices',
                'type' => 'textarea'
            ],
            [
                'setting' => 'theme',
                'value' => 'default',
                'description' => 'Application theme',
                'type' => 'select'
            ],
            [
                'setting' => 'session_timeout',
                'value' => '120',
                'description' => 'Session timeout in minutes',
                'type' => 'number'
            ],
            [
                'setting' => 'enable_registration',
                'value' => '0',
                'description' => 'Allow users to self-register',
                'type' => 'boolean'
            ],
            [
                'setting' => 'default_role',
                'value' => 'customer',
                'description' => 'Default role for new users',
                'type' => 'text'
            ],
            [
                'setting' => 'smtp_host',
                'value' => '',
                'description' => 'SMTP server host',
                'type' => 'text'
            ],
            [
                'setting' => 'smtp_port',
                'value' => '587',
                'description' => 'SMTP server port',
                'type' => 'number'
            ],
            [
                'setting' => 'smtp_username',
                'value' => '',
                'description' => 'SMTP username',
                'type' => 'text'
            ],
            [
                'setting' => 'smtp_password',
                'value' => '',
                'description' => 'SMTP password',
                'type' => 'password'
            ],
            [
                'setting' => 'smtp_encryption',
                'value' => 'tls',
                'description' => 'SMTP encryption type',
                'type' => 'select'
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert($setting);
        }
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
