<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('articulos')) {
            return;
        }

        $hasNewColumns = Schema::hasColumn('articulos', 'pasillo')
            && Schema::hasColumn('articulos', 'estanteria')
            && Schema::hasColumn('articulos', 'casillero');

        $hasOldColumns = Schema::hasColumn('articulos', 'ubicacion_pasillo')
            && Schema::hasColumn('articulos', 'ubicacion_estante')
            && Schema::hasColumn('articulos', 'ubicacion_casillero');

        if (! $hasNewColumns || ! $hasOldColumns) {
            return;
        }

        DB::statement("
            UPDATE articulos
            SET
                pasillo = COALESCE(pasillo, ubicacion_pasillo),
                estanteria = COALESCE(estanteria, ubicacion_estante),
                casillero = COALESCE(casillero, ubicacion_casillero)
            WHERE
                pasillo IS NULL
                OR estanteria IS NULL
                OR casillero IS NULL
        ");
    }

    public function down(): void
    {
        //
    }
};
