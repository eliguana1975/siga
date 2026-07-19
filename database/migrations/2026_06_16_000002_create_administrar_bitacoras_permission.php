<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Permission::findOrCreate('administrar-bitacoras', 'web');
    }

    public function down(): void
    {
        Permission::where('name', 'administrar-bitacoras')->delete();
    }
};
