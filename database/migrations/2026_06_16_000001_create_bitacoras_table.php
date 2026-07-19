<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();
            $table->string('accion', 80)->index();
            $table->string('modulo', 120)->nullable()->index();
            $table->string('entidad_type')->nullable()->index();
            $table->unsignedBigInteger('entidad_id')->nullable()->index();
            $table->text('descripcion');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 12)->nullable();
            $table->string('route_name')->nullable();
            $table->timestamps();

            $table->index(['entidad_type', 'entidad_id']);
            $table->index(['created_at', 'accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
