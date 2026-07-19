<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('flota_repuestos')) {
            return;
        }

        Schema::create('flota_repuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flota_id')->constrained('flota')->cascadeOnDelete();
            $table->foreignId('articulo_id')->nullable()->constrained('articulos')->nullOnDelete();
            $table->string('nombre_repuesto', 180)->nullable();
            $table->string('codigo_referencia', 100)->nullable();
            $table->string('marca', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->index(['flota_id', 'estado']);
            $table->index(['articulo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flota_repuestos');
    }
};
