<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('detalle_entrada') && Schema::hasColumn('detalle_entrada', 'observaciones')) {
            Schema::table('detalle_entrada', function (Blueprint $table) {
                $table->dropColumn('observaciones');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detalle_entrada') && ! Schema::hasColumn('detalle_entrada', 'observaciones')) {
            Schema::table('detalle_entrada', function (Blueprint $table) {
                $table->text('observaciones')->nullable()->after('precio_compra_unidad');
            });
        }
    }
};
