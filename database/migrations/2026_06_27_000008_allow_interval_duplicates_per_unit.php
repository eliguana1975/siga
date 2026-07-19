<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuracion_intervalos_servicio')) {
            return;
        }

        if ($this->hasIndex('configuracion_intervalos_servicio', 'cfg_intervalos_servicio_sistema_nombre_unique')) {
            Schema::table('configuracion_intervalos_servicio', function (Blueprint $table) {
                $table->dropUnique('cfg_intervalos_servicio_sistema_nombre_unique');
            });
        }

        if (! $this->hasIndex('configuracion_intervalos_servicio', 'cfg_intervalos_servicio_sistema_nombre_unidad_unique')) {
            Schema::table('configuracion_intervalos_servicio', function (Blueprint $table) {
                $table->unique(
                    ['sistema', 'nombre', 'unidad_intervalo'],
                    'cfg_intervalos_servicio_sistema_nombre_unidad_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuracion_intervalos_servicio')) {
            return;
        }

        if ($this->hasIndex('configuracion_intervalos_servicio', 'cfg_intervalos_servicio_sistema_nombre_unidad_unique')) {
            Schema::table('configuracion_intervalos_servicio', function (Blueprint $table) {
                $table->dropUnique('cfg_intervalos_servicio_sistema_nombre_unidad_unique');
            });
        }

        if (! $this->hasIndex('configuracion_intervalos_servicio', 'cfg_intervalos_servicio_sistema_nombre_unique')) {
            Schema::table('configuracion_intervalos_servicio', function (Blueprint $table) {
                $table->unique(['sistema', 'nombre'], 'cfg_intervalos_servicio_sistema_nombre_unique');
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        $database = DB::getDatabaseName();

        $result = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        return (bool) $result;
    }
};
