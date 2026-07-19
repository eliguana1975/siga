<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titular', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->text('direccion')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('tipo_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->timestamps();
        });

        Schema::create('cia_seguro', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('direccion')->nullable();
            $table->string('contacto', 150)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cia_seguro');
        Schema::dropIfExists('tipo_vehiculo');
        Schema::dropIfExists('titular');
    }
};
