<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ROLE_PERMISSIONS = [
        'MECANICO' => [
            'dashboard.ver',
            'chat.ver',
            'chat.crear',
            'controles-unidad.ver',
            'controles-unidad.crear',
            'ordenes-trabajo.ver',
            'ordenes-trabajo.crear',
            'ordenes-trabajo.editar',
            'ordenes-trabajo-articulos.agregar',
            'ordenes-trabajo-articulos.quitar',
            'solicitudes-repuestos.ver',
            'solicitudes-repuestos.crear',
            'reparaciones-articulos.ver',
            'reparaciones-articulos.editar',
        ],
        'JEFE DE TALLER' => [
            'dashboard.ver',
            'chat.ver',
            'chat.crear',
            'controles-unidad.ver',
            'controles-unidad.crear',
            'ordenes-trabajo.ver',
            'ordenes-trabajo.crear',
            'ordenes-trabajo.editar',
            'ordenes-trabajo-articulos.agregar',
            'ordenes-trabajo-articulos.quitar',
            'solicitudes-repuestos.ver',
            'solicitudes-repuestos.crear',
            'solicitudes-repuestos.editar',
            'solicitudes-repuestos.aprobar',
            'solicitudes-repuestos.rechazar',
            'reparaciones-articulos.ver',
            'reparaciones-articulos.crear',
            'reparaciones-articulos.editar',
            'reparaciones-articulos.imprimir',
            'reparaciones-articulos.reclamar',
            'flota.ver',
            'inventarios.ver',
            'entradas.ver',
        ],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (SystemPermissions::permissions() as $permission => $_label) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')
                ->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
