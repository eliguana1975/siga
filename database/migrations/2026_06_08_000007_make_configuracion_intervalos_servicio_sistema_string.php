<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('configuracion_intervalos_servicio')) {
            DB::statement("ALTER TABLE configuracion_intervalos_servicio MODIFY sistema VARCHAR(120) NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('configuracion_intervalos_servicio')) {
            DB::statement("ALTER TABLE configuracion_intervalos_servicio MODIFY sistema ENUM('motor','caja_automatica','diferencial','caja_transferencia_4x4') NOT NULL");
        }
    }
};
