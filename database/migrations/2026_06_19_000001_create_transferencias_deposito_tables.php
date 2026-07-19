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
        if (! Schema::hasTable('transferencias_deposito')) {
            Schema::create('transferencias_deposito', function (Blueprint $table) {
                $table->id();
                $table->foreignId('deposito_origen_id')->constrained('depositos')->restrictOnDelete();
                $table->foreignId('deposito_destino_id')->constrained('depositos')->restrictOnDelete();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('fecha_transferencia')->useCurrent();
                $table->enum('estado', ['confirmada', 'cancelada'])->default('confirmada');
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['deposito_origen_id', 'deposito_destino_id', 'usuario_id'], 'transf_dep_origen_destino_usuario_idx');
                $table->index('fecha_transferencia', 'transf_dep_fecha_idx');
            });
        }

        if (! Schema::hasTable('transferencia_deposito_detalles')) {
            Schema::create('transferencia_deposito_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transferencia_id')->constrained('transferencias_deposito')->cascadeOnDelete();
                $table->foreignId('inventario_origen_id')->nullable()->constrained('inventarios')->nullOnDelete();
                $table->foreignId('articulo_id')->constrained('articulos')->restrictOnDelete();
                $table->unsignedInteger('cantidad');
                $table->decimal('precio_compra_unidad', 12, 2)->nullable();
                $table->timestamps();

                $table->index(['transferencia_id', 'articulo_id'], 'transf_dep_det_transfer_art_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencia_deposito_detalles');
        Schema::dropIfExists('transferencias_deposito');
    }
};
