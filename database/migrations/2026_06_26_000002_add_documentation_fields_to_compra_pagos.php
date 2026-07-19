<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('compra_pagos', 'tipo_cheque')) {
                $table->string('tipo_cheque', 30)->nullable()->after('nro_cheque');
            }

            if (! Schema::hasColumn('compra_pagos', 'titular_cheque')) {
                $table->string('titular_cheque', 180)->nullable()->after('banco');
            }

            if (! Schema::hasColumn('compra_pagos', 'cuit_librador')) {
                $table->string('cuit_librador', 30)->nullable()->after('titular_cheque');
            }

            if (! Schema::hasColumn('compra_pagos', 'nro_cuenta_cheque')) {
                $table->string('nro_cuenta_cheque', 80)->nullable()->after('cuit_librador');
            }

            if (! Schema::hasColumn('compra_pagos', 'nro_operacion_cheque')) {
                $table->string('nro_operacion_cheque', 120)->nullable()->after('nro_cuenta_cheque');
            }

            if (! Schema::hasColumn('compra_pagos', 'fecha_comprobante_pago')) {
                $table->date('fecha_comprobante_pago')->nullable()->after('nro_recibo');
            }

            if (! Schema::hasColumn('compra_pagos', 'observaciones_comprobante')) {
                $table->text('observaciones_comprobante')->nullable()->after('fecha_comprobante_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            foreach ([
                'tipo_cheque',
                'titular_cheque',
                'cuit_librador',
                'nro_cuenta_cheque',
                'nro_operacion_cheque',
                'fecha_comprobante_pago',
                'observaciones_comprobante',
            ] as $column) {
                if (Schema::hasColumn('compra_pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
