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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('fecha_compra')->useCurrent();
            $table->decimal('total_compra', 12, 2)->default(0);
            $table->enum('estado', ['pendiente', 'aprobada', 'recibido', 'cancelado'])->default('pendiente');
            $table->string('comprobante', 100)->nullable();
            $table->text('notas')->nullable();

            $table->index(['deposito_id', 'proveedor_id', 'usuario_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
