<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_operativas', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('tipo', 80)->index();
            $table->string('severidad', 30)->default('media')->index();
            $table->string('titulo');
            $table->text('mensaje')->nullable();
            $table->string('url')->nullable();
            $table->string('entidad_type')->nullable()->index();
            $table->unsignedBigInteger('entidad_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'resolved_at']);
            $table->index(['severidad', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_operativas');
    }
};
