<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (! Schema::hasColumn('proveedores', 'impuestos')) {
                $table->json('impuestos')->nullable()->after('datos_pago');
            }
        });

        Schema::table('compra_pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('compra_pagos', 'importe_base')) {
                $table->decimal('importe_base', 15, 2)->nullable()->after('importe');
            }

            if (! Schema::hasColumn('compra_pagos', 'importe_impuestos')) {
                $table->decimal('importe_impuestos', 15, 2)->default(0)->after('importe_base');
            }

            if (! Schema::hasColumn('compra_pagos', 'impuestos_aplicados')) {
                $table->json('impuestos_aplicados')->nullable()->after('importe_impuestos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            foreach (['importe_base', 'importe_impuestos', 'impuestos_aplicados'] as $column) {
                if (Schema::hasColumn('compra_pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('proveedores', function (Blueprint $table) {
            if (Schema::hasColumn('proveedores', 'impuestos')) {
                $table->dropColumn('impuestos');
            }
        });
    }
};
