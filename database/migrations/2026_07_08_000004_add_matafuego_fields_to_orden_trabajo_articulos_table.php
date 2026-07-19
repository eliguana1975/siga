<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orden_trabajo_articulos')) {
            return;
        }

        Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
            if (! Schema::hasColumn('orden_trabajo_articulos', 'matafuego_numero')) {
                $table->string('matafuego_numero', 120)->nullable()->after('inventario_descontado');
            }

            if (! Schema::hasColumn('orden_trabajo_articulos', 'matafuego_fecha_vencimiento')) {
                $table->date('matafuego_fecha_vencimiento')->nullable()->after('matafuego_numero');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orden_trabajo_articulos')) {
            return;
        }

        Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
            foreach (['matafuego_fecha_vencimiento', 'matafuego_numero'] as $column) {
                if (Schema::hasColumn('orden_trabajo_articulos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
