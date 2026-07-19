<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cambios_cubiertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->cascadeOnDelete();
            $table->foreignId('flota_id')->constrained('flota')->restrictOnDelete();
            $table->foreignId('empleado_id')->constrained('empleados')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha');
            $table->integer('kilometraje')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('detalle_cambio_cubiertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cambio_cubierta_id')->constrained('cambios_cubiertas')->cascadeOnDelete();
            $table->foreignId('articulo_colocado_id')->nullable()->constrained('articulos')->restrictOnDelete();
            $table->foreignId('orden_trabajo_articulo_id')->nullable()->constrained('orden_trabajo_articulos')->nullOnDelete();
            $table->string('posicion', 10);
            $table->string('nro_cubierta_sacada', 80)->nullable();
            $table->string('nro_cubierta_colocada', 80)->nullable();
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_cambio_cubiertas');
        Schema::dropIfExists('cambios_cubiertas');
    }
};
