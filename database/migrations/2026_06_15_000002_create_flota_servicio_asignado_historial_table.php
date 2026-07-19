<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('flota', 'servicio_asignado_actual_id')) {
            Schema::table('flota', function (Blueprint $table) {
                $table->foreignId('servicio_asignado_actual_id')
                    ->nullable()
                    ->after('estado')
                    ->constrained('servicios_asignados')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('flota_servicio_asignado_historial')) {
            Schema::create('flota_servicio_asignado_historial', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flota_id')->constrained('flota')->cascadeOnDelete();
                $table->foreignId('servicio_asignado_id')->constrained('servicios_asignados')->restrictOnDelete();
                $table->date('fecha_desde');
                $table->date('fecha_hasta')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['flota_id', 'fecha_hasta'], 'fsah_flota_actual_idx');
                $table->index(['servicio_asignado_id', 'fecha_hasta'], 'fsah_servicio_actual_idx');
            });

            return;
        }

        Schema::table('flota_servicio_asignado_historial', function (Blueprint $table) {
            $table->index(['servicio_asignado_id', 'fecha_hasta'], 'fsah_servicio_actual_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flota_servicio_asignado_historial');

        Schema::table('flota', function (Blueprint $table) {
            $table->dropConstrainedForeignId('servicio_asignado_actual_id');
        });
    }
};
