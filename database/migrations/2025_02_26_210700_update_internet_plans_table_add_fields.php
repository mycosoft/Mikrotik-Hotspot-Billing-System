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
            // Add new columns
            if (!Schema::hasColumn('internet_plans', 'type')) {
                $table->string('type')->default('limited')->after('name'); // limited or unlimited
            }
            if (!Schema::hasColumn('internet_plans', 'limit_type')) {
                $table->string('limit_type')->nullable()->after('type'); // time, data, or both
            }
            if (!Schema::hasColumn('internet_plans', 'time_limit')) {
                $table->integer('time_limit')->nullable()->after('limit_type');
            }
            if (!Schema::hasColumn('internet_plans', 'data_limit')) {
                $table->bigInteger('data_limit')->nullable()->after('time_limit');
            }
            if (!Schema::hasColumn('internet_plans', 'simultaneous_sessions')) {
                $table->integer('simultaneous_sessions')->default(1)->after('data_limit');
            }
            
            // Add router relationship if not exists
            if (!Schema::hasColumn('internet_plans', 'router_id')) {
                $table->foreignId('router_id')->nullable()->constrained('routers');
            }
            
            // Drop old columns
            $table->dropColumn(['description', 'data_limit_mb', 'speed_limit_kbps', 'bandwidth_profile_id']);
            
            // Add new bandwidth relationship if not exists
            if (!Schema::hasColumn('internet_plans', 'bandwidth_id')) {
                $table->foreignId('bandwidth_id')->nullable()->constrained('bandwidths');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internet_plans', function (Blueprint $table) {
            // Restore old columns
            $table->text('description')->nullable();
            $table->integer('data_limit_mb')->nullable();
            $table->integer('speed_limit_kbps')->nullable();
            $table->foreignId('bandwidth_profile_id')->nullable()->constrained('bandwidth_profiles');
            
            // Remove new columns
            $table->dropColumn([
                'type',
                'limit_type',
                'time_limit',
                'data_limit',
                'simultaneous_sessions'
            ]);
            
            // Remove relationships if they were added
            if (Schema::hasColumn('internet_plans', 'router_id')) {
                $table->dropForeign(['router_id']);
                $table->dropColumn('router_id');
            }
            
            if (Schema::hasColumn('internet_plans', 'bandwidth_id')) {
                $table->dropForeign(['bandwidth_id']);
                $table->dropColumn('bandwidth_id');
            }
        });
    }
};
