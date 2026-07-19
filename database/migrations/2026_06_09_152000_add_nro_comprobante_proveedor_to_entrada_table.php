<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('entrada') && ! Schema::hasColumn('entrada', 'nro_comprobante_proveedor')) {
            Schema::table('entrada', function (Blueprint $table) {
                $table->string('nro_comprobante_proveedor', 100)->nullable()->after('nro_orden_compra');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('entrada') && Schema::hasColumn('entrada', 'nro_comprobante_proveedor')) {
            Schema::table('entrada', function (Blueprint $table) {
                $table->dropColumn('nro_comprobante_proveedor');
            });
        }
    }
};
