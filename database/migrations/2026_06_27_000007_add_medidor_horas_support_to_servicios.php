<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('flota')) {
            Schema::table('flota', function (Blueprint $table) {
                if (! Schema::hasColumn('flota', 'tipo_medidor_servicio')) {
                    $table->enum('tipo_medidor_servicio', ['km', 'horas'])
                        ->default('km')
                        ->after('estado');
                }

                if (! Schema::hasColumn('flota', 'horometro_actual')) {
                    $table->unsignedInteger('horometro_actual')
                        ->default(0)
                        ->after('tipo_medidor_servicio');
                }
            });
        }

        if (Schema::hasTable('configuracion_intervalos_servicio')) {
            Schema::table('configuracion_intervalos_servicio', function (Blueprint $table) {
                if (! Schema::hasColumn('configuracion_intervalos_servicio', 'unidad_intervalo')) {
                    $table->enum('unidad_intervalo', ['km', 'horas'])
                        ->default('km')
                        ->after('kilometros_intervalo');
                }
            });

            DB::table('configuracion_intervalos_servicio')
                ->whereNull('unidad_intervalo')
                ->update(['unidad_intervalo' => 'km']);
        }

        if (Schema::hasTable('registros_servicios_kilometraje')) {
            Schema::table('registros_servicios_kilometraje', function (Blueprint $table) {
                if (! Schema::hasColumn('registros_servicios_kilometraje', 'horometro_servicio')) {
                    $table->unsignedInteger('horometro_servicio')
                        ->nullable()
                        ->after('kilometraje_servicio');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('registros_servicios_kilometraje') && Schema::hasColumn('registros_servicios_kilometraje', 'horometro_servicio')) {
            Schema::table('registros_servicios_kilometraje', function (Blueprint $table) {
                $table->dropColumn('horometro_servicio');
            });
        }

        if (Schema::hasTable('configuracion_intervalos_servicio') && Schema::hasColumn('configuracion_intervalos_servicio', 'unidad_intervalo')) {
            Schema::table('configuracion_intervalos_servicio', function (Blueprint $table) {
                $table->dropColumn('unidad_intervalo');
            });
        }

        if (Schema::hasTable('flota')) {
            Schema::table('flota', function (Blueprint $table) {
                if (Schema::hasColumn('flota', 'horometro_actual')) {
                    $table->dropColumn('horometro_actual');
                }

                if (Schema::hasColumn('flota', 'tipo_medidor_servicio')) {
                    $table->dropColumn('tipo_medidor_servicio');
                }
            });
        }
    }
};
