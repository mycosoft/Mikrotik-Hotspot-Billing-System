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
        Schema::table('bandwidths', function (Blueprint $table) {
            // Drop existing speed columns
            $table->dropColumn(['upload_speed', 'download_speed']);
            
            // Add new speed columns with units
            $table->decimal('rate_up', 10, 2)->after('type');
            $table->string('rate_up_unit')->after('rate_up')->default('Mbps');
            $table->decimal('rate_down', 10, 2)->after('rate_up_unit');
            $table->string('rate_down_unit')->after('rate_down')->default('Mbps');
            
            // Add burst settings
            $table->string('burst_limit')->nullable()->after('rate_down_unit');
            $table->string('burst_threshold')->nullable()->after('burst_limit');
            $table->string('burst_time')->nullable()->after('burst_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bandwidths', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'rate_up', 'rate_up_unit',
                'rate_down', 'rate_down_unit',
                'burst_limit', 'burst_threshold', 'burst_time'
            ]);
            
            // Restore original columns
            $table->integer('upload_speed')->after('type');
            $table->integer('download_speed')->after('upload_speed');
        });
    }
};
