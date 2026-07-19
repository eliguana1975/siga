<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->boolean('vehiculo_parado')->default(false)->after('estado');
            $table->string('motivo_vehiculo_parado', 50)->nullable()->after('vehiculo_parado');
            $table->date('fecha_vehiculo_parado')->nullable()->after('motivo_vehiculo_parado');
            $table->text('observacion_vehiculo_parado')->nullable()->after('fecha_vehiculo_parado');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->dropColumn([
                'vehiculo_parado',
                'motivo_vehiculo_parado',
                'fecha_vehiculo_parado',
                'observacion_vehiculo_parado',
            ]);
        });
    }
};
