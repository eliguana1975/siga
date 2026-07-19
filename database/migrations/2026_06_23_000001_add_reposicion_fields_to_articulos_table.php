<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('articulos')) {
            return;
        }

        Schema::table('articulos', function (Blueprint $table) {
            if (! Schema::hasColumn('articulos', 'reposicion_modo')) {
                $table->enum('reposicion_modo', ['manual', 'automatico'])->default('manual')->after('stock_pedido');
            }

            if (! Schema::hasColumn('articulos', 'cantidad_pedido')) {
                $table->unsignedInteger('cantidad_pedido')->default(0)->after('reposicion_modo');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('articulos')) {
            return;
        }

        Schema::table('articulos', function (Blueprint $table) {
            if (Schema::hasColumn('articulos', 'cantidad_pedido')) {
                $table->dropColumn('cantidad_pedido');
            }

            if (Schema::hasColumn('articulos', 'reposicion_modo')) {
                $table->dropColumn('reposicion_modo');
            }
        });
    }
};
