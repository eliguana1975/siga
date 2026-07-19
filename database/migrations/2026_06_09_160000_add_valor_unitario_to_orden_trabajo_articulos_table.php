<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orden_trabajo_articulos') && ! Schema::hasColumn('orden_trabajo_articulos', 'valor_unitario')) {
            Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
                $table->decimal('valor_unitario', 12, 2)->default(0)->after('cantidad');
            });
        }

        if (
            Schema::hasTable('orden_trabajo_articulos')
            && Schema::hasTable('inventarios')
            && Schema::hasColumn('orden_trabajo_articulos', 'valor_unitario')
            && Schema::hasColumn('inventarios', 'precio_compra_unidad')
        ) {
            if (DB::connection()->getDriverName() !== 'mysql') {
                DB::table('orden_trabajo_articulos')
                    ->where('valor_unitario', 0)
                    ->orderBy('id')
                    ->chunkById(100, function ($items) {
                        foreach ($items as $item) {
                            $precio = DB::table('inventarios')
                                ->where('articulo_id', $item->articulo_id)
                                ->whereNotNull('precio_compra_unidad')
                                ->max('precio_compra_unidad') ?? 0;

                            DB::table('orden_trabajo_articulos')
                                ->where('id', $item->id)
                                ->update(['valor_unitario' => $precio]);
                        }
                    });

                return;
            }

            DB::statement("
                UPDATE orden_trabajo_articulos ota
                INNER JOIN (
                    SELECT articulo_id, MAX(precio_compra_unidad) AS precio_compra_unidad
                    FROM inventarios
                    WHERE precio_compra_unidad IS NOT NULL
                    GROUP BY articulo_id
                ) precios ON precios.articulo_id = ota.articulo_id
                SET ota.valor_unitario = COALESCE(precios.precio_compra_unidad, 0)
                WHERE ota.valor_unitario = 0
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orden_trabajo_articulos') && Schema::hasColumn('orden_trabajo_articulos', 'valor_unitario')) {
            Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
                $table->dropColumn('valor_unitario');
            });
        }
    }
};
