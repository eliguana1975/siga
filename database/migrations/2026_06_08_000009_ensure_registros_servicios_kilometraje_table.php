<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('registros_servicios_kilometraje')) {
            Schema::create('registros_servicios_kilometraje', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('flota_id');
                $table->unsignedBigInteger('configuracion_intervalo_servicio_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedInteger('kilometraje_servicio');
                $table->dateTime('fecha_servicio')->useCurrent();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('flota_id', 'reg_serv_km_flota_fk')->references('id')->on('flota')->cascadeOnDelete();
                $table->foreign('configuracion_intervalo_servicio_id', 'reg_serv_km_intervalo_fk')->references('id')->on('configuracion_intervalos_servicio')->cascadeOnDelete();
                $table->foreign('user_id', 'reg_serv_km_user_fk')->references('id')->on('users')->nullOnDelete();
                $table->index(['flota_id', 'configuracion_intervalo_servicio_id'], 'reg_serv_km_flota_intervalo_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_servicios_kilometraje');
    }
};
