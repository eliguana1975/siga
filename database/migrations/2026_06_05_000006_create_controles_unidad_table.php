<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('interno', 50);
            $table->string('conductor', 150);
            $table->unsignedInteger('kilometraje_actual');
            $table->string('servicio_asignado', 150);
            $table->text('observaciones_generales');
            $table->json('partes');
            $table->json('control_unidad');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_unidad');
    }
};
