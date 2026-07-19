<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            // Replace global unique on nro_cubierta_colocada with a composite unique
            // that enforces uniqueness per cambio_cubierta_id.
            if (Schema::hasColumn('detalle_cambio_cubiertas', 'nro_cubierta_colocada')) {
                // Drop existing unique index if present
                try {
                    $table->dropUnique('detalle_cambio_cubiertas_nro_colocada_unique');
                } catch (\Exception $e) {
                    // ignore if index does not exist
                }
            }

            // Add composite unique index
            $table->unique(['cambio_cubierta_id', 'nro_cubierta_colocada'], 'detalle_cambio_cubiertas_cambio_nro_unique');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            // Restore previous global unique (if desired)
            try {
                $table->dropUnique('detalle_cambio_cubiertas_cambio_nro_unique');
            } catch (\Exception $e) {
            }

            $table->unique('nro_cubierta_colocada', 'detalle_cambio_cubiertas_nro_colocada_unique');
        });
    }
};
