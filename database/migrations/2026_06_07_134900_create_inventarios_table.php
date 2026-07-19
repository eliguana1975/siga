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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('deposito_id');
            $table->unsignedBigInteger('articulo_id');

            $table->decimal('precio_compra_unidad', 12, 2)->nullable();
            $table->unsignedInteger('cantidad')->nullable();
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->unsignedInteger('stock_maximo')->default(0);
            $table->timestamp('fecha_registro')->useCurrent();

            $table->enum('estado', ['compra', 'ajuste', 'traslado'])->default('compra');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
