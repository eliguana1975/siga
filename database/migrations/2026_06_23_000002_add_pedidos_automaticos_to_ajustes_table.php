<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ajustes')) {
            return;
        }

        Schema::table('ajustes', function (Blueprint $table) {
            if (! Schema::hasColumn('ajustes', 'pedidos_automaticos_activos')) {
                $table->boolean('pedidos_automaticos_activos')->default(false)->after('web');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ajustes')) {
            return;
        }

        Schema::table('ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('ajustes', 'pedidos_automaticos_activos')) {
                $table->dropColumn('pedidos_automaticos_activos');
            }
        });
    }
};
