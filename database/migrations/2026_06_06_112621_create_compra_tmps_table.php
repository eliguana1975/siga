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
        Schema::create('tmp_compra', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();

            $table->decimal('precio_compra_unidad', 12, 2);
            $table->integer('cantidad');
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->enum('estado', ['activo', 'inactivo', 'pendiente', 'confirmado', 'cancelado'])->default('activo');

            $table->index(['usuario_id', 'deposito_id', 'articulo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_compra');
    }
};
