<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'inventarios.etiquetas' => 'inventarios.ver',
            'ordenes-compra.enviar-mail' => 'ordenes-compra.ver',
        ];

        foreach ($permissions as $permission => $basePermission) {
            Permission::findOrCreate($permission, 'web');

            Role::permission($basePermission)
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($permission));
        }

        Role::query()
            ->whereIn('name', ['ADMIN', 'ADMINISTRADOR', 'SUPERUSUARIO', 'SUPER USER', 'SUPERUSER'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo(array_keys($permissions)));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['inventarios.etiquetas', 'ordenes-compra.enviar-mail'] as $permission) {
            Permission::where('name', $permission)->where('guard_name', 'web')->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
