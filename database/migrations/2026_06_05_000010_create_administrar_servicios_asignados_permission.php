<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Permission::findOrCreate('administrar-servicios-asignados', 'web');
    }

    public function down(): void
    {
        Permission::where('name', 'administrar-servicios-asignados')->delete();
    }
};
