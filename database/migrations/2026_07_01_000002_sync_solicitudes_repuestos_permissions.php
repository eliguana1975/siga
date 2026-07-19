<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        foreach (array_keys(SystemPermissions::permissions()) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        Role::query()
            ->whereIn('name', ['ADMIN', 'ADMINISTRADOR', 'SUPERUSUARIO', 'SUPER ADMIN', 'SUPERADMIN', 'JEFE DE COMPRAS'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo(array_keys(SystemPermissions::permissions())));

        Role::query()
            ->whereIn('name', ['SUPERVISOR', 'JEFE DE TALLER', 'TALLER'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo([
                'solicitudes-repuestos.ver',
                'solicitudes-repuestos.crear',
                'solicitudes-repuestos.editar',
            ]));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ([
            'solicitudes-repuestos.ver',
            'solicitudes-repuestos.crear',
            'solicitudes-repuestos.editar',
            'solicitudes-repuestos.aprobar',
            'solicitudes-repuestos.rechazar',
            'solicitudes-repuestos.catalogar',
            'solicitudes-repuestos.generar-pedido',
            'solicitudes-repuestos.cerrar',
            'solicitudes-repuestos.exportar',
            'solicitudes-repuestos.imprimir',
        ] as $permissionName) {
            Permission::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
