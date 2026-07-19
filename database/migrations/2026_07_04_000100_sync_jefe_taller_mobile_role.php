<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ROLE_NAME = 'JEFE DE TALLER';

    private const PERMISSIONS = [
        'dashboard.ver',
        'chat.ver',
        'chat.crear',
        'ordenes-trabajo.ver',
        'ordenes-trabajo.crear',
        'ordenes-trabajo.editar',
        'ordenes-trabajo-articulos.agregar',
        'ordenes-trabajo-articulos.quitar',
        'controles-unidad.ver',
        'controles-unidad.crear',
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
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (SystemPermissions::permissions() as $permission => $_label) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate(self::ROLE_NAME, 'web')
            ->syncPermissions(self::PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()
            ->where('name', self::ROLE_NAME)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
