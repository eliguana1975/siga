<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const NEW_PERMISSIONS = [
        'bi.ver',
        'notificaciones-operativas.ver',
        'auditoria-operativa.ver',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::NEW_PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::query()
            ->whereHas('permissions', fn ($query) => $query->where('name', 'dashboard.ver'))
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo(self::NEW_PERMISSIONS));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->whereIn('name', self::NEW_PERMISSIONS)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
