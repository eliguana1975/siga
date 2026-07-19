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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(SystemPermissions::PERMISSIONS) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        Role::query()
            ->whereIn('name', ['ADMIN', 'ADMINISTRADOR', 'SUPERUSUARIO', 'SUPER USER', 'SUPERUSER'])
            ->get()
            ->each(fn (Role $role) => $role->syncPermissions(array_keys(SystemPermissions::PERMISSIONS)));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['entregas-ropa-epp.ver', 'entregas-ropa-epp.crear', 'entregas-ropa-epp.editar'] as $permissionName) {
            Permission::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
