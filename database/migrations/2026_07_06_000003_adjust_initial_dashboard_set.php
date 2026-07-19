<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('dashboards')->updateOrInsert(
            ['key' => 'general'],
            [
                'name' => 'General',
                'description' => 'Dashboard original del sistema.',
                'icon' => 'bi bi-speedometer2',
                'is_active' => true,
                'sort_order' => 10,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('dashboards')->where('key', 'compras')->update([
            'key' => 'compras-inventario',
            'name' => 'Compras e Inventario',
            'description' => 'Seguimiento de compras, solicitudes, reparaciones e inventario.',
            'icon' => 'bi bi-box-seam',
            'is_active' => true,
            'sort_order' => 20,
            'updated_at' => $now,
        ]);

        DB::table('dashboards')->updateOrInsert(
            ['key' => 'compras-inventario'],
            [
                'name' => 'Compras e Inventario',
                'description' => 'Seguimiento de compras, solicitudes, reparaciones e inventario.',
                'icon' => 'bi bi-box-seam',
                'is_active' => true,
                'sort_order' => 20,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('dashboards')->updateOrInsert(
            ['key' => 'choferes'],
            [
                'name' => 'Choferes',
                'description' => 'Dashboard reservado para choferes.',
                'icon' => 'bi bi-person-vcard',
                'is_active' => true,
                'sort_order' => 30,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('dashboards')->updateOrInsert(
            ['key' => 'taller'],
            [
                'name' => 'Taller',
                'description' => 'Ordenes de trabajo, vehiculos parados y servicios pendientes.',
                'icon' => 'bi bi-tools',
                'is_active' => true,
                'sort_order' => 40,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('dashboards')
            ->whereIn('key', ['compras', 'inventario', 'flota', 'operativo'])
            ->update(['is_active' => false, 'updated_at' => $now]);

        $adminRoleIds = DB::table('roles')
            ->whereIn(DB::raw('UPPER(name)'), ['ADMIN', 'ADMINISTRADOR', 'SUPERUSUARIO', 'SUPER USUARIO', 'SUPERUSER', 'SUPER USER'])
            ->pluck('id');

        $dashboardIds = DB::table('dashboards')
            ->whereIn('key', ['general', 'compras-inventario', 'choferes', 'taller'])
            ->pluck('id');

        foreach ($dashboardIds as $dashboardId) {
            foreach ($adminRoleIds as $roleId) {
                DB::table('dashboard_role')->updateOrInsert(
                    ['dashboard_id' => $dashboardId, 'role_id' => $roleId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('dashboards')->where('key', 'choferes')->delete();
    }
};
