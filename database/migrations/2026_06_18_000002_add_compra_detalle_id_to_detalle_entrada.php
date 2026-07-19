<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('detalle_entrada') && ! Schema::hasColumn('detalle_entrada', 'compra_detalle_id')) {
            Schema::table('detalle_entrada', function (Blueprint $table) {
                $table->foreignId('compra_detalle_id')
                    ->nullable()
                    ->after('entrada_id')
                    ->constrained('compra_detalles')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detalle_entrada') && Schema::hasColumn('detalle_entrada', 'compra_detalle_id')) {
            Schema::table('detalle_entrada', function (Blueprint $table) {
                $table->dropConstrainedForeignId('compra_detalle_id');
            });
        }
    }
};
