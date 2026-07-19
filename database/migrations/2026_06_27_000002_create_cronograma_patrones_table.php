<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronograma_patrones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->unsignedSmallInteger('dias_trabajo');
            $table->unsignedSmallInteger('dias_descanso');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronograma_patrones');
    }
};
