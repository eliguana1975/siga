<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bases', function (Blueprint $table) {
            // Agregar columnas si no existen
            if (!Schema::hasColumn('bases', 'provincia_id')) {
                $table->foreignId('provincia_id')->after('deposito_id')->constrained('provincias')->onDelete('restrict');
            }
            if (!Schema::hasColumn('bases', 'ciudad_id')) {
                $table->foreignId('ciudad_id')->after('provincia_id')->constrained('ciudades')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bases', function (Blueprint $table) {
            if (Schema::hasColumn('bases', 'provincia_id')) {
                $table->dropForeignIdFor('Provincia', 'provincia_id');
            }
            if (Schema::hasColumn('bases', 'ciudad_id')) {
                $table->dropForeignIdFor('Ciudad', 'ciudad_id');
            }
        });
    }
};
