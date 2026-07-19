<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('compra_pagos')) {
            Schema::create('compra_pagos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
                $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('forma_pago', 40);
                $table->decimal('importe', 12, 2)->default(0);
                $table->date('fecha_pago')->nullable();
                $table->string('nro_cheque', 120)->nullable();
                $table->string('banco', 150)->nullable();
                $table->date('fecha_emision_cheque')->nullable();
                $table->date('fecha_vencimiento_cheque')->nullable();
                $table->string('nro_comprobante_pago', 120)->nullable();
                $table->string('nro_transferencia', 120)->nullable();
                $table->string('nro_recibo', 120)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['compra_id', 'forma_pago']);
                $table->index(['proveedor_id', 'fecha_pago']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_pagos');
    }
};
