<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Router;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add a temporary column for router_id
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('router_id')->nullable();
        });

        // Get all routers with their names and IDs
        $routers = Router::pluck('id', 'name')->toArray();
        
        // Update all vouchers to use router IDs in the temporary column
        $vouchers = Voucher::whereNotNull('routers')->get();
        foreach ($vouchers as $voucher) {
            if (isset($routers[$voucher->routers])) {
                $voucher->router_id = $routers[$voucher->routers];
                $voucher->save();
            }
        }

        // Drop the old routers column
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('routers');
        });

        // Rename router_id to routers
        Schema::table('vouchers', function (Blueprint $table) {
            $table->renameColumn('router_id', 'routers');
        });

        // Add foreign key constraint
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreign('routers')
                  ->references('id')
                  ->on('routers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraint
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['routers']);
        });

        // Add temporary column
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('router_name')->nullable();
        });

        // Get all routers
        $routers = Router::pluck('name', 'id')->toArray();

        // Convert IDs back to names
        $vouchers = Voucher::whereNotNull('routers')->get();
        foreach ($vouchers as $voucher) {
            if (isset($routers[$voucher->routers])) {
                $voucher->router_name = $routers[$voucher->routers];
                $voucher->save();
            }
        }

        // Drop the routers column
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('routers');
        });

        // Create new routers column as string
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('routers')->nullable();
        });

        // Copy data from router_name to routers
        DB::table('vouchers')
            ->whereNotNull('router_name')
            ->update(['routers' => DB::raw('router_name')]);

        // Drop the temporary column
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('router_name');
        });
    }
};
