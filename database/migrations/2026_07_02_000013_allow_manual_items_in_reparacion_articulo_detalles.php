<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparacion_articulo_detalles', function (Blueprint $table) {
            if (! Schema::hasColumn('reparacion_articulo_detalles', 'descripcion_articulo_manual')) {
                $table->string('descripcion_articulo_manual', 255)->nullable()->after('articulo_id');
            }

            if (! Schema::hasColumn('reparacion_articulo_detalles', 'codigo_articulo_manual')) {
                $table->string('codigo_articulo_manual', 120)->nullable()->after('descripcion_articulo_manual');
            }

            if (Schema::hasColumn('reparacion_articulo_detalles', 'articulo_id')) {
                $table->dropForeign(['articulo_id']);
                $table->unsignedBigInteger('articulo_id')->nullable()->change();
                $table->foreign('articulo_id')->references('id')->on('articulos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reparacion_articulo_detalles', function (Blueprint $table) {
            if (Schema::hasColumn('reparacion_articulo_detalles', 'articulo_id')) {
                $table->dropForeign(['articulo_id']);
                $table->unsignedBigInteger('articulo_id')->nullable(false)->change();
                $table->foreign('articulo_id')->references('id')->on('articulos')->restrictOnDelete();
            }

            if (Schema::hasColumn('reparacion_articulo_detalles', 'codigo_articulo_manual')) {
                $table->dropColumn('codigo_articulo_manual');
            }

            if (Schema::hasColumn('reparacion_articulo_detalles', 'descripcion_articulo_manual')) {
                $table->dropColumn('descripcion_articulo_manual');
            }
        });
    }
};
