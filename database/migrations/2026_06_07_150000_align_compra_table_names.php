<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $renamedTmpCompra = false;
        $renamedCompraDetalle = false;

        if (Schema::hasTable('compra_tmps') && ! Schema::hasTable('tmp_compra')) {
            Schema::rename('compra_tmps', 'tmp_compra');
            $renamedTmpCompra = true;
        }

        if (Schema::hasTable('compra_detalles') && ! Schema::hasTable('compra_detalles')) {
            Schema::rename('compra_detalles', 'compra_detalles');
            $renamedCompraDetalle = true;
        }

        if ($renamedTmpCompra) {
            Schema::table('tmp_compra', function (Blueprint $table) {
                $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('deposito_id')->references('id')->on('depositos')->cascadeOnDelete();
                $table->foreign('articulo_id')->references('id')->on('articulos')->cascadeOnDelete();
            });
        }

        if ($renamedCompraDetalle) {
            Schema::table('compra_detalles', function (Blueprint $table) {
                $table->foreign('compra_id')->references('id')->on('compras')->cascadeOnDelete();
                $table->foreign('articulo_id')->references('id')->on('articulos')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Compatibility migration only. Avoid undoing constraints created by fresh installs.
    }
};
