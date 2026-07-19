<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach (array_keys(SystemPermissions::PERMISSIONS) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::query()
            ->whereIn('name', ['ADMIN', 'Administrador'])
            ->get()
            ->each(fn (Role $role) => $role->syncPermissions(array_keys(SystemPermissions::PERMISSIONS)));
    }

    public function down(): void
    {
        foreach (['cronogramas.ver', 'cronogramas.crear', 'cronogramas.editar', 'cronogramas.eliminar', 'cronogramas.administrar', 'administrar-cronogramas'] as $permission) {
            Permission::query()->where('name', $permission)->where('guard_name', 'web')->delete();
        }
    }
};
