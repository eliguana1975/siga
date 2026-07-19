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
        // No crear si la tabla ya existe
        if (Schema::hasTable('articulos')) {
            return;
        }

        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->foreignId('unidad_medida_id')->constrained('unidad_medidas')->onDelete('restrict');
            
            $table->string('nombre', 255);
            $table->string('codigo_producto', 255)->nullable();
            $table->string('foto_articulo_1', 255)->nullable();
            $table->string('foto_articulo_2', 255)->nullable();
            $table->string('foto_articulo_3', 255)->nullable();
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_maximo')->default(0);
            $table->integer('stock_pedido')->default(0);
            $table->string('pasillo', 50)->nullable();
            $table->string('estanteria', 50)->nullable();
            $table->string('casillero', 50)->nullable();
            $table->enum('estado_item', ['activo', 'inactivo'])->default('activo');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
