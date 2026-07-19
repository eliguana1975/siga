<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuracion_vencimientos_verificacion')) {
            Schema::create('configuracion_vencimientos_verificacion', function (Blueprint $table) {
                $table->id();
                $table->string('tipo', 80);
                $table->string('nombre', 120);
                $table->unsignedSmallInteger('dias_alerta')->default(30);
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->unique('tipo', 'cfg_vto_verificacion_tipo_unique');
            });
        }

        if (! Schema::hasTable('registros_verificaciones_tecnicas')) {
            Schema::create('registros_verificaciones_tecnicas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('flota_id');
                $table->unsignedBigInteger('configuracion_vencimiento_verificacion_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->date('fecha_emision')->nullable();
                $table->date('fecha_vencimiento');
                $table->string('comprobante', 120)->nullable();
                $table->enum('estado', ['vigente', 'renovada', 'cancelada'])->default('vigente');
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('flota_id', 'reg_verif_flota_fk')->references('id')->on('flota')->cascadeOnDelete();
                $table->foreign('configuracion_vencimiento_verificacion_id', 'reg_verif_config_fk')->references('id')->on('configuracion_vencimientos_verificacion')->cascadeOnDelete();
                $table->foreign('user_id', 'reg_verif_user_fk')->references('id')->on('users')->nullOnDelete();
                $table->index(['flota_id', 'configuracion_vencimiento_verificacion_id'], 'reg_verif_flota_config_idx');
                $table->index('fecha_vencimiento', 'reg_verif_vencimiento_idx');
            });
        }

        DB::table('configuracion_vencimientos_verificacion')->insertOrIgnore([
            [
                'tipo' => 'TECNICA NACIONAL',
                'nombre' => 'VERIFICACION TECNICA NACIONAL',
                'dias_alerta' => 30,
                'estado' => 'activo',
                'observaciones' => 'VENCIMIENTO DE VERIFICACION TECNICA NACIONAL.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'TECNICA PROVINCIAL',
                'nombre' => 'VERIFICACION TECNICA PROVINCIAL',
                'dias_alerta' => 30,
                'estado' => 'activo',
                'observaciones' => 'VENCIMIENTO DE VERIFICACION TECNICA PROVINCIAL.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'CNRT',
                'nombre' => 'CNRT',
                'dias_alerta' => 45,
                'estado' => 'activo',
                'observaciones' => 'CONTROL DE VENCIMIENTO ASOCIADO A CNRT.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_verificaciones_tecnicas');
        Schema::dropIfExists('configuracion_vencimientos_verificacion');
    }
};
