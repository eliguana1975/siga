<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('compra_pagos', 'tipo_pago')) {
                $table->string('tipo_pago', 30)->nullable()->after('forma_pago');
            }

            if (! Schema::hasColumn('compra_pagos', 'porcentaje_pago')) {
                $table->decimal('porcentaje_pago', 8, 4)->nullable()->after('tipo_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            foreach (['tipo_pago', 'porcentaje_pago'] as $column) {
                if (Schema::hasColumn('compra_pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
