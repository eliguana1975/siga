<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            if (! Schema::hasColumn('detalle_cambio_cubiertas', 'estado_cubierta_sacada')) {
                $table->string('estado_cubierta_sacada', 40)->nullable()->after('nro_cubierta_sacada');
            }

            if (! Schema::hasColumn('detalle_cambio_cubiertas', 'destino_cubierta_sacada')) {
                $table->string('destino_cubierta_sacada', 40)->nullable()->after('estado_cubierta_sacada');
            }

            if (! Schema::hasColumn('detalle_cambio_cubiertas', 'motivo_baja_cubierta_sacada')) {
                $table->string('motivo_baja_cubierta_sacada', 180)->nullable()->after('destino_cubierta_sacada');
            }

            if (! Schema::hasColumn('detalle_cambio_cubiertas', 'observacion_cubierta_sacada')) {
                $table->text('observacion_cubierta_sacada')->nullable()->after('motivo_baja_cubierta_sacada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            foreach ([
                'estado_cubierta_sacada',
                'destino_cubierta_sacada',
                'motivo_baja_cubierta_sacada',
                'observacion_cubierta_sacada',
            ] as $column) {
                if (Schema::hasColumn('detalle_cambio_cubiertas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
