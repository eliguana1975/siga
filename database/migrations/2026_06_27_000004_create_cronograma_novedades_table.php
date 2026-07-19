<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronograma_novedades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['trabaja', 'descanso', 'franco_compensatorio', 'licencia', 'feriado', 'otro']);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->unique(['empleado_id', 'fecha']);
            $table->index(['fecha', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronograma_novedades');
    }
};
