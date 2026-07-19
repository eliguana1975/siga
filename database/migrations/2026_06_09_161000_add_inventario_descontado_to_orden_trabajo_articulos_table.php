<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orden_trabajo_articulos') && ! Schema::hasColumn('orden_trabajo_articulos', 'inventario_descontado')) {
            Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
                $table->boolean('inventario_descontado')->default(false)->after('valor_unitario');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orden_trabajo_articulos') && Schema::hasColumn('orden_trabajo_articulos', 'inventario_descontado')) {
            Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
                $table->dropColumn('inventario_descontado');
            });
        }
    }
};
