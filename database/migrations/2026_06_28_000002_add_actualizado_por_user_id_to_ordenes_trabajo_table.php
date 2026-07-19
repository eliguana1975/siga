<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            if (! Schema::hasColumn('ordenes_trabajo', 'actualizado_por_user_id')) {
                $table->foreignId('actualizado_por_user_id')
                    ->nullable()
                    ->after('empleado_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_trabajo', 'actualizado_por_user_id')) {
                $table->dropConstrainedForeignId('actualizado_por_user_id');
            }
        });
    }
};
