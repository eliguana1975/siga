<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $legacyMap = [
        'categorias.administrar' => ['categorias.ver', 'categorias.crear', 'categorias.editar', 'categorias.eliminar'],
        'unidad-medidas.administrar' => ['unidad-medidas.ver', 'unidad-medidas.crear', 'unidad-medidas.editar', 'unidad-medidas.eliminar'],
        'inventarios.administrar' => ['inventarios.ver', 'inventarios.crear', 'inventarios.editar', 'inventarios.eliminar', 'inventario-transferencias.ver', 'inventario-transferencias.crear'],
        'depositos.administrar' => ['depositos.ver', 'depositos.crear', 'depositos.editar', 'depositos.eliminar'],
        'ajustes.administrar' => ['ajustes.ver', 'ajustes.crear', 'ajustes.editar'],
        'bases.administrar' => ['bases.ver', 'bases.crear', 'bases.editar', 'bases.eliminar'],
        'servicios-asignados.administrar' => ['servicios-asignados.ver', 'servicios-asignados.crear', 'servicios-asignados.editar', 'servicios-asignados.eliminar'],
        'roles.administrar' => ['roles.ver', 'roles.crear', 'roles.editar', 'roles.eliminar'],
        'users.administrar' => ['users.ver', 'users.crear', 'users.editar', 'users.eliminar'],
        'empleados.administrar' => ['empleados.ver', 'empleados.crear', 'empleados.editar', 'empleados.eliminar'],
        'provincias.administrar' => ['provincias.ver', 'provincias.crear', 'provincias.editar', 'provincias.eliminar'],
        'ciudades.administrar' => ['ciudades.ver', 'ciudades.crear', 'ciudades.editar', 'ciudades.eliminar'],
        'configuracion-intervalos-servicio.administrar' => ['configuracion-intervalos-servicio.ver', 'configuracion-intervalos-servicio.crear', 'configuracion-intervalos-servicio.editar', 'configuracion-intervalos-servicio.eliminar'],
        'configuracion-vencimientos-verificacion.administrar' => ['configuracion-vencimientos-verificacion.ver', 'configuracion-vencimientos-verificacion.crear', 'configuracion-vencimientos-verificacion.editar', 'configuracion-vencimientos-verificacion.eliminar'],
        'titulares.administrar' => ['titulares.ver', 'titulares.crear', 'titulares.editar', 'titulares.eliminar'],
        'marca-vehiculo.administrar' => ['marca-vehiculo.ver', 'marca-vehiculo.crear', 'marca-vehiculo.editar', 'marca-vehiculo.eliminar'],
        'cia-seguro.administrar' => ['cia-seguro.ver', 'cia-seguro.crear', 'cia-seguro.editar', 'cia-seguro.eliminar'],
        'tipo-vehiculo.administrar' => ['tipo-vehiculo.ver', 'tipo-vehiculo.crear', 'tipo-vehiculo.editar', 'tipo-vehiculo.eliminar'],
        'marca-carroceria.administrar' => ['marca-carroceria.ver', 'marca-carroceria.crear', 'marca-carroceria.editar', 'marca-carroceria.eliminar'],
        'tipo-motor.administrar' => ['tipo-motor.ver', 'tipo-motor.crear', 'tipo-motor.editar', 'tipo-motor.eliminar'],
        'modelo-motor.administrar' => ['modelo-motor.ver', 'modelo-motor.crear', 'modelo-motor.editar', 'modelo-motor.eliminar'],
        'tipo-caja.administrar' => ['tipo-caja.ver', 'tipo-caja.crear', 'tipo-caja.editar', 'tipo-caja.eliminar'],
        'modelo-caja.administrar' => ['modelo-caja.ver', 'modelo-caja.crear', 'modelo-caja.editar', 'modelo-caja.eliminar'],
    ];

    public function up(): void
    {
        foreach (array_keys(SystemPermissions::PERMISSIONS) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($this->legacyMap as $legacyPermission => $newPermissions) {
            Role::permission($legacyPermission)->get()->each(function (Role $role) use ($newPermissions) {
                $role->givePermissionTo($newPermissions);
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
