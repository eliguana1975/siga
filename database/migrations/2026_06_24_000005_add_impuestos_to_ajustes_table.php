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
            if (! Schema::hasColumn('ajustes', 'impuestos')) {
                $table->json('impuestos')->nullable()->after('pedidos_automaticos_activos');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ajustes')) {
            return;
        }

        Schema::table('ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('ajustes', 'impuestos')) {
                $table->dropColumn('impuestos');
            }
        });
    }
};
