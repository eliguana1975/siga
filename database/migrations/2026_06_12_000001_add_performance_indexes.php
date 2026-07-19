<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('ordenes_trabajo', 'ordenes_flota_km_idx', ['flota_id', 'kilometraje']);
        $this->addIndex('controles_unidad', 'controles_flota_km_idx', ['flota_id', 'kilometraje_actual']);
        $this->addIndex('registros_servicios_kilometraje', 'reg_serv_km_flota_intervalo_km_idx', [
            'flota_id',
            'configuracion_intervalo_servicio_id',
            'kilometraje_servicio',
        ]);
        $this->addIndex('flota', 'flota_estado_interno_idx', ['estado', 'nro_interno']);
        $this->addIndex('articulos', 'articulos_nombre_idx', ['nombre']);
    }

    public function down(): void
    {
        $this->dropIndex('articulos', 'articulos_nombre_idx');
        $this->dropIndex('flota', 'flota_estado_interno_idx');
        $this->dropIndex('registros_servicios_kilometraje', 'reg_serv_km_flota_intervalo_km_idx');
        $this->dropIndex('controles_unidad', 'controles_flota_km_idx');
        $this->dropIndex('ordenes_trabajo', 'ordenes_flota_km_idx');
    }

    private function addIndex(string $table, string $indexName, array $columns): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndex(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
