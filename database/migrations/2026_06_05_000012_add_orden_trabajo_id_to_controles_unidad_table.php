<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controles_unidad', function (Blueprint $table) {
            $table->foreignId('orden_trabajo_id')
                ->nullable()
                ->after('servicio_asignado_id')
                ->constrained('ordenes_trabajo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('controles_unidad', function (Blueprint $table) {
            $table->dropConstrainedForeignId('orden_trabajo_id');
        });
    }
};
