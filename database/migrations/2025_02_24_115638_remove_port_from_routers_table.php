<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Router;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing IP addresses to include port
        $routers = Router::all();
        foreach ($routers as $router) {
            if (strpos($router->ip_address, ':') === false) {
                $router->ip_address = $router->ip_address . ':' . ($router->port ?? 8728);
                $router->save();
            }
        }

        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn('port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->integer('port')->default(8728)->after('ip_address');
        });

        // Split IP addresses back to separate port
        $routers = Router::all();
        foreach ($routers as $router) {
            $parts = explode(':', $router->ip_address);
            if (count($parts) === 2) {
                $router->ip_address = $parts[0];
                $router->port = intval($parts[1]);
                $router->save();
            }
        }
    }
};
