<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('entrada')) {
            Schema::create('entrada', function (Blueprint $table) {
                $table->id();
                $table->foreignId('compra_id')->nullable()->constrained('compras')->nullOnDelete();
                $table->foreignId('deposito_id')->constrained('depositos')->restrictOnDelete();
                $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nro_orden_compra', 100)->nullable();
                $table->dateTime('fecha_entrada')->useCurrent();
                $table->decimal('total_entrada', 12, 2)->default(0);
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['compra_id', 'deposito_id', 'proveedor_id', 'usuario_id']);
                $table->index('fecha_entrada');
            });
        }

        if (! Schema::hasTable('detalle_entrada')) {
            Schema::create('detalle_entrada', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entrada_id')->constrained('entrada')->cascadeOnDelete();
                $table->foreignId('articulo_id')->constrained('articulos')->restrictOnDelete();
                $table->unsignedInteger('cantidad');
                $table->decimal('precio_compra_unidad', 12, 2)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['entrada_id', 'articulo_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_entrada');
        Schema::dropIfExists('entrada');
    }
};
