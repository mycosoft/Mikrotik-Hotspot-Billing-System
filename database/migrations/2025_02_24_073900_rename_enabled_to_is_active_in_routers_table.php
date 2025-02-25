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
        // Check if enabled column exists
        if (Schema::hasColumn('routers', 'enabled')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->dropColumn('enabled');
            });
        }

        // Check if is_active column doesn't exist
        if (!Schema::hasColumn('routers', 'is_active')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if is_active column exists
        if (Schema::hasColumn('routers', 'is_active')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        // Check if enabled column doesn't exist
        if (!Schema::hasColumn('routers', 'enabled')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->boolean('enabled')->default(true)->after('password');
            });
        }
    }
};