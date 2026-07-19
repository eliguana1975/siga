<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            $table->unique('nro_cubierta_colocada', 'detalle_cambio_cubiertas_nro_colocada_unique');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            $table->dropUnique('detalle_cambio_cubiertas_nro_colocada_unique');
        });
    }
};
