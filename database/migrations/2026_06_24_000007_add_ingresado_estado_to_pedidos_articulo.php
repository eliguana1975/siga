<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedidos_articulo')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pedidos_articulo MODIFY estado ENUM('pendiente', 'confirmado', 'ingresado', 'cancelado') DEFAULT 'pendiente'");
        }

        DB::table('pedidos_articulo')
            ->where('estado', 'confirmado')
            ->orderBy('id')
            ->chunkById(100, function ($pedidos) {
                foreach ($pedidos as $pedido) {
                    $compras = DB::table('compras')
                        ->where('pedido_articulo_id', $pedido->id)
                        ->where('estado', '!=', 'cancelado')
                        ->pluck('estado');

                    if ($compras->isNotEmpty() && $compras->every(fn ($estado) => $estado === 'recibido')) {
                        DB::table('pedidos_articulo')
                            ->where('id', $pedido->id)
                            ->update(['estado' => 'ingresado']);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pedidos_articulo')) {
            return;
        }

        DB::table('pedidos_articulo')
            ->where('estado', 'ingresado')
            ->update(['estado' => 'confirmado']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pedidos_articulo MODIFY estado ENUM('pendiente', 'confirmado', 'cancelado') DEFAULT 'pendiente'");
        }
    }
};
