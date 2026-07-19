<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('registros_servicios_kilometraje')) {
            return;
        }

        Schema::table('registros_servicios_kilometraje', function (Blueprint $table) {
            if (!$this->indexExists('registros_servicios_kilometraje', 'reg_serv_km_flota_intervalo_index')) {
                $table->index(['flota_id', 'configuracion_intervalo_servicio_id'], 'reg_serv_km_flota_intervalo_index');
            }
        });

        if (!$this->foreignKeyExists('registros_servicios_kilometraje', 'reg_serv_km_intervalo_fk')) {
            DB::statement('ALTER TABLE registros_servicios_kilometraje ADD CONSTRAINT reg_serv_km_intervalo_fk FOREIGN KEY (configuracion_intervalo_servicio_id) REFERENCES configuracion_intervalos_servicio(id) ON DELETE CASCADE');
        }

        if (!$this->foreignKeyExists('registros_servicios_kilometraje', 'reg_serv_km_user_fk')) {
            DB::statement('ALTER TABLE registros_servicios_kilometraje ADD CONSTRAINT reg_serv_km_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('registros_servicios_kilometraje')) {
            return;
        }

        Schema::table('registros_servicios_kilometraje', function (Blueprint $table) {
            if ($this->foreignKeyExists('registros_servicios_kilometraje', 'reg_serv_km_intervalo_fk')) {
                $table->dropForeign('reg_serv_km_intervalo_fk');
            }

            if ($this->foreignKeyExists('registros_servicios_kilometraje', 'reg_serv_km_user_fk')) {
                $table->dropForeign('reg_serv_km_user_fk');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return !empty(DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $index]
        ));
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return !empty(DB::select(
            'SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = ? LIMIT 1',
            [$table, $constraint, 'FOREIGN KEY']
        ));
    }
};
