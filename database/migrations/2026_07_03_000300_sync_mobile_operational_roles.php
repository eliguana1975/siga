<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ROLE_PERMISSIONS = [
        'CHOFER' => [
            'dashboard.ver',
            'controles-unidad.ver',
            'controles-unidad.crear',
        ],
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
        'SUPERVISOR' => [
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
            'solicitudes-repuestos.catalogar',
            'solicitudes-repuestos.generar-pedido',
            'solicitudes-repuestos.cerrar',
            'reparaciones-articulos.ver',
            'reparaciones-articulos.crear',
            'reparaciones-articulos.editar',
            'reparaciones-articulos.imprimir',
            'reparaciones-articulos.reclamar',
            'flota.ver',
            'servicios-kilometraje.ver',
            'verificaciones-tecnicas.ver',
            'historial-articulos-vehiculo.ver',
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

        foreach (array_keys(self::ROLE_PERMISSIONS) as $roleName) {
            Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
