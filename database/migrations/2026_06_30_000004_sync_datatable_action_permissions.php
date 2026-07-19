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

        foreach (SystemPermissions::DATATABLE_ACTION_MODULES as [$module]) {
            $viewPermission = "{$module}.ver";
            $actionPermissions = ["{$module}.exportar", "{$module}.imprimir"];

            Role::permission($viewPermission)
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($actionPermissions));
        }

        Role::query()
            ->whereIn('name', ['ADMIN', 'ADMINISTRADOR', 'SUPER ADMIN', 'SUPERADMIN'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo(array_keys(SystemPermissions::permissions())));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissions = [];

        foreach (SystemPermissions::DATATABLE_ACTION_MODULES as [$module]) {
            $permissions[] = "{$module}.exportar";
            $permissions[] = "{$module}.imprimir";
        }

        Permission::query()
            ->whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
