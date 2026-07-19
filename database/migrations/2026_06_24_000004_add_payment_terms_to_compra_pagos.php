<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            $table->string('plazo_pago', 80)->nullable()->after('fecha_vencimiento_cheque');
            $table->text('vencimientos_pago')->nullable()->after('plazo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            $table->dropColumn(['plazo_pago', 'vencimientos_pago']);
        });
    }
};
