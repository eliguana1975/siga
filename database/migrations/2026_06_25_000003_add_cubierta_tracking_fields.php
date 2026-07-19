<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flota_cubierta_ejes', function (Blueprint $table) {
            if (! Schema::hasColumn('flota_cubierta_ejes', 'articulo_cubierta_id')) {
                $table->foreignId('articulo_cubierta_id')->nullable()->after('tipo_eje')->constrained('articulos')->nullOnDelete();
            }
        });

        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            if (! Schema::hasColumn('detalle_cambio_cubiertas', 'cubierta_sacada_id')) {
                $table->foreignId('cubierta_sacada_id')->nullable()->after('posicion')->constrained('cubiertas')->nullOnDelete();
            }

            if (! Schema::hasColumn('detalle_cambio_cubiertas', 'cubierta_colocada_id')) {
                $table->foreignId('cubierta_colocada_id')->nullable()->after('articulo_colocado_id')->constrained('cubiertas')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cambio_cubiertas', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_cambio_cubiertas', 'cubierta_colocada_id')) {
                $table->dropConstrainedForeignId('cubierta_colocada_id');
            }

            if (Schema::hasColumn('detalle_cambio_cubiertas', 'cubierta_sacada_id')) {
                $table->dropConstrainedForeignId('cubierta_sacada_id');
            }
        });

        Schema::table('flota_cubierta_ejes', function (Blueprint $table) {
            if (Schema::hasColumn('flota_cubierta_ejes', 'articulo_cubierta_id')) {
                $table->dropConstrainedForeignId('articulo_cubierta_id');
            }
        });
    }
};
