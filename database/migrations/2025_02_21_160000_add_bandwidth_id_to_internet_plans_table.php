<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBandwidthIdToInternetPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bandwidths', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->default('Shared'); // Shared, Dedicated
            $table->integer('upload_speed')->nullable(); // in Kbps
            $table->integer('download_speed')->nullable(); // in Kbps
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Update internet_plans table to reference bandwidth_profiles
        Schema::table('internet_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('bandwidth_id')->nullable()->after('id');
            $table->foreign('bandwidth_id')
                  ->references('id')
                  ->on('bandwidth_profiles')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_plans', function (Blueprint $table) {
            $table->dropForeign(['bandwidth_id']);
            $table->dropColumn('bandwidth_id');
        });

        Schema::dropIfExists('bandwidths');
    }
}
