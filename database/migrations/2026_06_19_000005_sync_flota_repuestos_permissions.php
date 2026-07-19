<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'flota-repuestos.ver',
            'flota-repuestos.crear',
            'flota-repuestos.editar',
            'flota-repuestos.eliminar',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ([
            'flota-repuestos.ver',
            'flota-repuestos.crear',
            'flota-repuestos.editar',
            'flota-repuestos.eliminar',
        ] as $permission) {
            Permission::where('name', $permission)->where('guard_name', 'web')->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
