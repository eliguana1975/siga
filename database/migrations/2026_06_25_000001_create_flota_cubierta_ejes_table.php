<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flota_cubierta_ejes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flota_id')->constrained('flota')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_eje');
            $table->string('tipo_eje', 30)->default('trasero');
            $table->unsignedTinyInteger('cubiertas_izquierda')->default(1);
            $table->unsignedTinyInteger('cubiertas_derecha')->default(1);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['flota_id', 'numero_eje']);
            $table->index(['flota_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flota_cubierta_ejes');
    }
};
