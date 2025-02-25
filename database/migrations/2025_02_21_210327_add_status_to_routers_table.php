<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->enum('status', ['online', 'offline'])->default('offline')->after('is_active');
            $table->timestamp('last_seen')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_seen']);
        });
    }
};
