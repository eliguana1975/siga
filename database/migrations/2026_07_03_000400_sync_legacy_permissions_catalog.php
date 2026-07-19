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

        foreach (array_keys(SystemPermissions::permissions()) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::query()
            ->whereIn('name', ['SUPERUSUARIO', 'ADMIN'])
            ->get()
            ->each(function (Role $role): void {
                $role->givePermissionTo(array_keys(SystemPermissions::permissions()));
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
