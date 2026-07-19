<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_motor_id')->constrained('tipo_motor');
            $table->foreignId('modelo_motor_id')->constrained('modelo_motor');
            $table->foreignId('cod_marca_carroceria_id')->constrained('marca_carroceria');
            $table->foreignId('cod_titular_id')->constrained('titular');
            $table->foreignId('cod_tipo_vehiculo_id')->constrained('tipo_vehiculo');
            $table->foreignId('cod_cia_seguro_id')->constrained('cia_seguro');
            $table->foreignId('modelo_caja_id')->constrained('modelo_caja');
            $table->foreignId('tipo_caja_id')->constrained('tipo_caja');
            $table->foreignId('marca_vehiculo_id')->constrained('marca_vehiculo');

            $table->string('tipo_aceite_motor', 50);
            $table->string('tipo_aceite_caja', 50);
            $table->string('nro_interno', 50)->unique();
            $table->string('dominio', 20)->unique();
            $table->enum('estado', ['activo', 'baja', 'mantenimiento'])->default('activo');
            $table->string('nro_motor', 50)->unique();
            $table->string('nro_chasis', 50)->unique();
            $table->integer('cant_aceite_motor');
            $table->integer('cant_aceite_caja');
            $table->string('med_cub_delanteras', 50);
            $table->string('med_cub_traseras', 50);
            $table->integer('cantidad_pasajeros')->nullable();
            $table->integer('anio_fabricacion')->nullable();
            $table->string('nro_poliza', 100)->nullable();
            $table->enum('estado_seguro', ['Activo', 'Baja'])->default('Activo');
            $table->text('observaciones')->nullable();
            $table->string('foto_flota', 255)->nullable();
            $table->string('foto_flota_2', 255)->nullable();
            $table->string('foto_flota_3', 255)->nullable();
            $table->string('foto_flota_4', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flota');
    }
};
