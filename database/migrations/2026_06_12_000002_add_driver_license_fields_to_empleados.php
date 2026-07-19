<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empleados')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'categoria_carnet_conducir')) {
                $table->string('categoria_carnet_conducir', 50)->nullable()->after('fecha_nacimiento');
            }

            if (! Schema::hasColumn('empleados', 'vencimiento_carnet_conducir')) {
                $table->date('vencimiento_carnet_conducir')->nullable()->after('categoria_carnet_conducir');
            }

            if (! Schema::hasColumn('empleados', 'vencimiento_linti')) {
                $table->date('vencimiento_linti')->nullable()->after('vencimiento_carnet_conducir');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empleados')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table) {
            foreach (['vencimiento_linti', 'vencimiento_carnet_conducir', 'categoria_carnet_conducir'] as $column) {
                if (Schema::hasColumn('empleados', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
