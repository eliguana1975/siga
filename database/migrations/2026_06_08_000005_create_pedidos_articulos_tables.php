<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tmp_pedido_articulo')) {
            Schema::create('tmp_pedido_articulo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
                $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
                $table->integer('cantidad');
                $table->dateTime('fecha_creacion')->useCurrent();
                $table->enum('estado', ['activo', 'inactivo', 'pendiente', 'confirmado', 'cancelado'])->default('activo');
                $table->timestamps();

                $table->index(['usuario_id', 'deposito_id', 'articulo_id']);
            });
        }

        if (!Schema::hasTable('pedidos_articulo')) {
            Schema::create('pedidos_articulo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('fecha_pedido')->useCurrent();
                $table->enum('estado', ['pendiente', 'confirmado', 'cancelado'])->default('pendiente');
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pedido_detalle_articulo')) {
            Schema::create('pedido_detalle_articulo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pedidos_articulo_id')->constrained('pedidos_articulo')->cascadeOnDelete();
                $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
                $table->integer('cantidad');
                $table->timestamps();

                $table->index(['pedidos_articulo_id', 'articulo_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_detalle_articulo');
        Schema::dropIfExists('pedidos_articulo');
        Schema::dropIfExists('tmp_pedido_articulo');
    }
};
