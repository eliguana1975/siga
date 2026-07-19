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
        if (Schema::hasTable('tmp_compra') && ! Schema::hasColumn('tmp_compra', 'proveedor_id')) {
            Schema::table('tmp_compra', function (Blueprint $table) {
                $table->foreignId('proveedor_id')
                    ->nullable()
                    ->after('articulo_id')
                    ->constrained('proveedores')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('compra_detalles') && ! Schema::hasColumn('compra_detalles', 'proveedor_id')) {
            Schema::table('compra_detalles', function (Blueprint $table) {
                $table->foreignId('proveedor_id')
                    ->nullable()
                    ->after('articulo_id')
                    ->constrained('proveedores')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tmp_compra') && Schema::hasColumn('tmp_compra', 'proveedor_id')) {
            Schema::table('tmp_compra', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proveedor_id');
            });
        }

        if (Schema::hasTable('compra_detalles') && Schema::hasColumn('compra_detalles', 'proveedor_id')) {
            Schema::table('compra_detalles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('proveedor_id');
            });
        }
    }
};
