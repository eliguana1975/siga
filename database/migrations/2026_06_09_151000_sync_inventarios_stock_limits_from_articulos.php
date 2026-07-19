<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('inventarios')
            || ! Schema::hasTable('articulos')
            || ! Schema::hasColumn('inventarios', 'articulo_id')
            || ! Schema::hasColumn('inventarios', 'stock_minimo')
            || ! Schema::hasColumn('inventarios', 'stock_maximo')
            || ! Schema::hasColumn('articulos', 'stock_minimo')
            || ! Schema::hasColumn('articulos', 'stock_maximo')
        ) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                UPDATE inventarios
                INNER JOIN articulos ON articulos.id = inventarios.articulo_id
                SET
                    inventarios.stock_minimo = COALESCE(articulos.stock_minimo, 0),
                    inventarios.stock_maximo = COALESCE(articulos.stock_maximo, 0)
            ");

            return;
        }

        DB::table('inventarios')
            ->join('articulos', 'articulos.id', '=', 'inventarios.articulo_id')
            ->select([
                'inventarios.id',
                'articulos.stock_minimo',
                'articulos.stock_maximo',
            ])
            ->orderBy('inventarios.id')
            ->chunkById(100, function ($inventarios) {
                foreach ($inventarios as $inventario) {
                    DB::table('inventarios')
                        ->where('id', $inventario->id)
                        ->update([
                            'stock_minimo' => $inventario->stock_minimo ?? 0,
                            'stock_maximo' => $inventario->stock_maximo ?? 0,
                        ]);
                }
            }, 'inventarios.id', 'id');
    }

    public function down(): void
    {
        //
    }
};
