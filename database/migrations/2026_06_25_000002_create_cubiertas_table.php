<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cubiertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->restrictOnDelete();
            $table->foreignId('inventario_id')->nullable()->constrained('inventarios')->nullOnDelete();
            $table->foreignId('deposito_id')->nullable()->constrained('depositos')->nullOnDelete();
            $table->foreignId('entrada_id')->nullable()->constrained('entrada')->nullOnDelete();
            $table->foreignId('detalle_entrada_id')->nullable()->constrained('detalle_entrada')->nullOnDelete();
            $table->foreignId('flota_id')->nullable()->constrained('flota')->nullOnDelete();
            $table->string('posicion', 20)->nullable();
            $table->string('medida', 120);
            $table->unsignedInteger('secuencia');
            $table->string('numero', 80)->unique();
            $table->string('estado', 30)->default('nueva');
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['articulo_id', 'secuencia']);
            $table->index(['articulo_id', 'estado']);
            $table->index(['flota_id', 'posicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cubiertas');
    }
};
