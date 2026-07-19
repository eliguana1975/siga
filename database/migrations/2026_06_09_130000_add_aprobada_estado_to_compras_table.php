<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('compras') || ! Schema::hasColumn('compras', 'estado')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE compras MODIFY estado ENUM('pendiente','aprobada','recibido','cancelado') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('compras') || ! Schema::hasColumn('compras', 'estado')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE compras MODIFY estado ENUM('pendiente','recibido','cancelado') NOT NULL DEFAULT 'pendiente'");
    }
};
