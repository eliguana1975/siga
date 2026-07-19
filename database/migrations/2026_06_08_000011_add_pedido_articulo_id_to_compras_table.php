<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('compras') || Schema::hasColumn('compras', 'pedido_articulo_id')) {
            return;
        }

        Schema::table('compras', function (Blueprint $table) {
            $table->foreignId('pedido_articulo_id')
                ->nullable()
                ->after('proveedor_id')
                ->constrained('pedidos_articulo')
                ->nullOnDelete();

            $table->index('pedido_articulo_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('compras') || ! Schema::hasColumn('compras', 'pedido_articulo_id')) {
            return;
        }

        Schema::table('compras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pedido_articulo_id');
        });
    }
};
