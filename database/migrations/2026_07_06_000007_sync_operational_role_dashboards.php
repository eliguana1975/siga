<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $assignments = [
            'taller' => ['MECANICO', 'JEFE DE TALLER'],
            'choferes' => ['CHOFER'],
        ];

        foreach ($assignments as $dashboardKey => $roleNames) {
            $dashboardId = DB::table('dashboards')->where('key', $dashboardKey)->value('id');

            if (! $dashboardId) {
                continue;
            }

            $roleIds = DB::table('roles')->whereIn('name', $roleNames)->pluck('id');

            foreach ($roleIds as $roleId) {
                DB::table('dashboard_role')->updateOrInsert(
                    ['dashboard_id' => $dashboardId, 'role_id' => $roleId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        //
    }
};
