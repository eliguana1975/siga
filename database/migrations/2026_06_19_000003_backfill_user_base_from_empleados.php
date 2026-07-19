<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'base_id') || ! Schema::hasTable('empleados')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                UPDATE users
                INNER JOIN empleados ON empleados.usuario_id = users.id
                SET users.base_id = empleados.base_id
                WHERE users.base_id IS NULL
                  AND empleados.base_id IS NOT NULL
            ");

            return;
        }

        DB::table('users')
            ->join('empleados', 'empleados.usuario_id', '=', 'users.id')
            ->whereNull('users.base_id')
            ->whereNotNull('empleados.base_id')
            ->select(['users.id', 'empleados.base_id'])
            ->orderBy('users.id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['base_id' => $user->base_id]);
                }
            }, 'users.id', 'id');
    }

    public function down(): void
    {
        // No revertimos este backfill para no borrar configuraciones ajustadas manualmente.
    }
};
