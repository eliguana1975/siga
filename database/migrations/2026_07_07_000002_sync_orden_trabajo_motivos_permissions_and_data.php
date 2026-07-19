<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $guard = 'web';
        $permissions = [
            'ordenes-trabajo-motivos.ver',
            'ordenes-trabajo-motivos.crear',
            'ordenes-trabajo-motivos.editar',
            'ordenes-trabajo-motivos.eliminar',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        Role::query()
            ->whereIn('name', ['SUPERUSUARIO', 'ADMINISTRADOR', 'ADMINISTRADOR GENERAL'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        $now = now();
        $motivos = [
            ['codigo' => 'documentacion', 'nombre' => 'DOCUMENTACION'],
            ['codigo' => 'mecanica', 'nombre' => 'MECANICA'],
            ['codigo' => 'electricidad', 'nombre' => 'ELECTRICIDAD'],
            ['codigo' => 'cubiertas', 'nombre' => 'CUBIERTAS'],
            ['codigo' => 'carroceria', 'nombre' => 'CARROCERIA'],
            ['codigo' => 'accesorios', 'nombre' => 'ACCESORIOS'],
            ['codigo' => 'service', 'nombre' => 'SERVICE'],
            ['codigo' => 'otro', 'nombre' => 'OTRO'],
        ];

        foreach ($motivos as $motivo) {
            DB::table('orden_trabajo_motivos')->updateOrInsert(
                ['codigo' => $motivo['codigo']],
                [
                    'nombre' => $motivo['nombre'],
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
