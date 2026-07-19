<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controles_unidad', function (Blueprint $table) {
            $table->foreignId('servicio_asignado_id')
                ->nullable()
                ->after('kilometraje_actual')
                ->constrained('servicios_asignados')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('controles_unidad', function (Blueprint $table) {
            $table->dropConstrainedForeignId('servicio_asignado_id');
        });
    }
};
