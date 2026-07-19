<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('configuracion_intervalos_servicio')) {
            Schema::create('configuracion_intervalos_servicio', function (Blueprint $table) {
                $table->id();
                $table->enum('sistema', ['motor', 'caja_automatica', 'diferencial', 'caja_transferencia_4x4']);
                $table->string('nombre', 120);
                $table->unsignedInteger('kilometros_intervalo');
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->unique(['sistema', 'nombre'], 'cfg_intervalos_servicio_sistema_nombre_unique');
            });
        }

        DB::table('configuracion_intervalos_servicio')->insertOrIgnore([
            [
                'sistema' => 'motor',
                'nombre' => 'Servicio de motor',
                'kilometros_intervalo' => 10000,
                'estado' => 'activo',
                'observaciones' => 'Intervalo general para cambio de aceite y servicio de motor.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sistema' => 'caja_automatica',
                'nombre' => 'Servicio de caja automatica',
                'kilometros_intervalo' => 40000,
                'estado' => 'activo',
                'observaciones' => 'Intervalo general para servicio de cajas automaticas.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sistema' => 'diferencial',
                'nombre' => 'Servicio de diferencial',
                'kilometros_intervalo' => 40000,
                'estado' => 'activo',
                'observaciones' => 'Intervalo general para diferenciales.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sistema' => 'caja_transferencia_4x4',
                'nombre' => 'Servicio de caja de transferencia 4x4',
                'kilometros_intervalo' => 40000,
                'estado' => 'activo',
                'observaciones' => 'Intervalo general para caja de transferencia en unidades 4x4.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_intervalos_servicio');
    }
};
