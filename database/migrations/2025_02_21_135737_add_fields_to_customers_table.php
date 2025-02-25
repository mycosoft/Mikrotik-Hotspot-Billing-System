<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add only missing fields
            $table->string('service_type')->nullable()->after('balance');
            $table->string('pppoe_username')->nullable()->after('service_type');
            $table->string('pppoe_password')->nullable()->after('pppoe_username');
            $table->string('ip_address')->nullable()->after('pppoe_password');
            $table->enum('status', ['Active', 'Inactive', 'Suspended'])->default('Active')->after('ip_address');
            $table->decimal('latitude', 10, 8)->nullable()->after('status');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'service_type',
                'pppoe_username',
                'pppoe_password',
                'ip_address',
                'status',
                'latitude',
                'longitude'
            ]);
        });
    }
};
