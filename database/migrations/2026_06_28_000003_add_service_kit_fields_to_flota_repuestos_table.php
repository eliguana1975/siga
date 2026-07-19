<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flota_repuestos')) {
            return;
        }

        Schema::table('flota_repuestos', function (Blueprint $table) {
            if (! Schema::hasColumn('flota_repuestos', 'configuracion_intervalo_servicio_id')) {
                $table->foreignId('configuracion_intervalo_servicio_id')
                    ->nullable()
                    ->after('articulo_id')
                    ->constrained('configuracion_intervalos_servicio')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('flota_repuestos', 'cantidad_servicio')) {
                $table->unsignedInteger('cantidad_servicio')->default(1)->after('configuracion_intervalo_servicio_id');
            }

            if (! Schema::hasColumn('flota_repuestos', 'modo_carga_servicio')) {
                $table->string('modo_carga_servicio', 20)->default('manual')->after('cantidad_servicio');
            }

            if (! Schema::hasColumn('flota_repuestos', 'obligatorio_servicio')) {
                $table->boolean('obligatorio_servicio')->default(false)->after('modo_carga_servicio');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('flota_repuestos')) {
            return;
        }

        Schema::table('flota_repuestos', function (Blueprint $table) {
            if (Schema::hasColumn('flota_repuestos', 'configuracion_intervalo_servicio_id')) {
                $table->dropConstrainedForeignId('configuracion_intervalo_servicio_id');
            }

            foreach (['cantidad_servicio', 'modo_carga_servicio', 'obligatorio_servicio'] as $column) {
                if (Schema::hasColumn('flota_repuestos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
