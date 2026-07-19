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
        if (Schema::hasTable('bases')) {
            return;
        }

        Schema::create('bases', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('deposito_id')->constrained('depositos')->onDelete('cascade');
            $table->foreignId('provincia_id')->constrained('provincias')->onDelete('restrict');
            $table->foreignId('ciudad_id')->constrained('ciudades')->onDelete('restrict');
            
            $table->string('nombre', 150);
            $table->text('direccion')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bases');
    }
};
