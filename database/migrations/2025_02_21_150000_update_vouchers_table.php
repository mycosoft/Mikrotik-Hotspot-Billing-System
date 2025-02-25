<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Add new columns
            $table->string('type')->nullable()->after('id');
            $table->string('routers')->nullable()->after('type');
            $table->boolean('status')->default(false)->after('code');
            $table->unsignedBigInteger('generated_by')->nullable()->after('used_by');
            
            // Add foreign key constraints
            $table->foreign('generated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Remove unnecessary columns
            $table->dropColumn(['price', 'validity_days']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['generated_by']);
            
            // Remove new columns
            $table->dropColumn(['type', 'routers', 'status', 'generated_by']);
        });

        Schema::table('vouchers', function (Blueprint $table) {
            // Add back removed columns
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('validity_days')->nullable();
        });
    }
}
