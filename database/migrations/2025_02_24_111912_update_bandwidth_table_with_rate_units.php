<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First check if old columns exist
        if (Schema::hasColumn('bandwidths', 'upload_speed')) {
            // Add new columns
            Schema::table('bandwidths', function (Blueprint $table) {
                $table->decimal('rate_up', 10, 2)->after('type');
                $table->string('rate_up_unit')->default('Mbps')->after('rate_up');
                $table->decimal('rate_down', 10, 2)->after('rate_up_unit');
                $table->string('rate_down_unit')->default('Mbps')->after('rate_down');
                $table->string('burst_limit')->nullable()->after('rate_down_unit');
                $table->string('burst_threshold')->nullable()->after('burst_limit');
                $table->string('burst_time')->nullable()->after('burst_threshold');
            });

            // Convert existing speeds to new format
            DB::statement('UPDATE bandwidths SET 
                rate_up = ROUND(upload_speed / 1024, 2),
                rate_down = ROUND(download_speed / 1024, 2)
            ');

            // Drop old columns
            Schema::table('bandwidths', function (Blueprint $table) {
                $table->dropColumn(['upload_speed', 'download_speed']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if new columns exist
        if (Schema::hasColumn('bandwidths', 'rate_up')) {
            // Add back old columns
            Schema::table('bandwidths', function (Blueprint $table) {
                $table->integer('upload_speed')->after('type');
                $table->integer('download_speed')->after('upload_speed');
            });

            // Convert back to old format
            DB::statement('UPDATE bandwidths SET 
                upload_speed = ROUND(rate_up * (CASE WHEN rate_up_unit = "Kbps" THEN 1 ELSE 1024 END)),
                download_speed = ROUND(rate_down * (CASE WHEN rate_down_unit = "Kbps" THEN 1 ELSE 1024 END))
            ');

            // Drop new columns
            Schema::table('bandwidths', function (Blueprint $table) {
                $table->dropColumn([
                    'rate_up', 'rate_up_unit',
                    'rate_down', 'rate_down_unit',
                    'burst_limit', 'burst_threshold', 'burst_time'
                ]);
            });
        }
    }
};
