<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ciudades') && !Schema::hasColumn('ciudades', 'nombre')) {
            Schema::table('ciudades', function (Blueprint $table) {
                $table->string('nombre')->nullable()->unique()->after('id');
            });
        }

        if (!Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provincia_id')->nullable()->constrained('provincias')->nullOnDelete();
                $table->foreignId('ciudades_id')->nullable()->constrained('ciudades')->nullOnDelete();
                $table->string('nombre', 255);
                $table->string('telefono', 50)->nullable();
                $table->string('email', 150)->nullable();
                $table->text('direccion')->nullable();
                $table->string('contacto', 150)->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');

        if (Schema::hasTable('ciudades') && Schema::hasColumn('ciudades', 'nombre')) {
            Schema::table('ciudades', function (Blueprint $table) {
                $table->dropColumn('nombre', 255);
            });
        }
    }
};
