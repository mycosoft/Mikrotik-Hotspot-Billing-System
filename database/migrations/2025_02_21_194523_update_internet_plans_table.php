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
        Schema::table('internet_plans', function (Blueprint $table) {
            // Drop existing columns
            $table->dropColumn(['description', 'data_limit_mb', 'speed_limit_kbps']);

            // Add new columns
            $table->string('type')->default('limited')->after('name'); // limited or unlimited
            $table->string('limit_type')->nullable()->after('type'); // time, data, or both
            $table->integer('time_limit')->nullable()->after('limit_type'); // in minutes
            $table->integer('data_limit')->nullable()->after('time_limit'); // in MB
            $table->foreignId('router_id')->nullable()->after('data_limit')->constrained('routers');
            $table->integer('simultaneous_sessions')->default(1)->after('bandwidth_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internet_plans', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'type',
                'limit_type',
                'time_limit',
                'data_limit',
                'router_id',
                'simultaneous_sessions'
            ]);

            // Add back original columns
            $table->text('description')->nullable();
            $table->integer('data_limit_mb')->nullable();
            $table->integer('speed_limit_kbps')->nullable();
        });
    }
};
