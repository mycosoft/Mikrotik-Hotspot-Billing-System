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
        // Add deleted_at column to bandwidths table
        Schema::table('bandwidths', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Fix foreign key in internet_plans table
        Schema::table('internet_plans', function (Blueprint $table) {
            $table->dropForeign(['bandwidth_id']);
            $table->foreign('bandwidth_id')
                  ->references('id')
                  ->on('bandwidths')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bandwidths', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('internet_plans', function (Blueprint $table) {
            $table->dropForeign(['bandwidth_id']);
            $table->foreign('bandwidth_id')
                  ->references('id')
                  ->on('bandwidth_profiles')
                  ->onDelete('set null');
        });
    }
};
