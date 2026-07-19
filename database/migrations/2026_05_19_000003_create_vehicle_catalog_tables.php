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
        foreach (['tipo_motor', 'modelo_motor', 'tipo_caja', 'modelo_caja', 'marca_carroceria'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 255)->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['marca_carroceria', 'modelo_caja', 'tipo_caja', 'modelo_motor', 'tipo_motor'] as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
