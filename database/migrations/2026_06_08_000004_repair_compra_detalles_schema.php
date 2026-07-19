<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('compra_detalles')) {
            Schema::create('compra_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('compra_id')->nullable()->constrained('compras')->cascadeOnDelete();
                $table->foreignId('articulo_id')->nullable()->constrained('articulos')->cascadeOnDelete();
                $table->decimal('precio_compra_unidad', 12, 2)->default(0);
                $table->integer('cantidad')->default(1);
                $table->timestamps();
            });

            return;
        }

        Schema::table('compra_detalles', function (Blueprint $table) {
            if (! Schema::hasColumn('compra_detalles', 'compra_id')) {
                $table->foreignId('compra_id')->nullable()->after('id')->constrained('compras')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('compra_detalles', 'articulo_id')) {
                $table->foreignId('articulo_id')->nullable()->after('compra_id')->constrained('articulos')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('compra_detalles', 'precio_compra_unidad')) {
                $table->decimal('precio_compra_unidad', 12, 2)->default(0)->after('articulo_id');
            }

            if (! Schema::hasColumn('compra_detalles', 'cantidad')) {
                $table->integer('cantidad')->default(1)->after('precio_compra_unidad');
            }

            if (! Schema::hasColumn('compra_detalles', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Compatibility migration. Do not remove repaired columns.
    }
};
