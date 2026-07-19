<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_conversaciones')) {
            Schema::create('chat_conversaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->unique(['user_one_id', 'user_two_id']);
                $table->index('last_message_at');
            });
        }

        if (! Schema::hasTable('chat_mensajes')) {
            Schema::create('chat_mensajes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_conversacion_id')->constrained('chat_conversaciones')->cascadeOnDelete();
                $table->foreignId('emisor_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('receptor_id')->constrained('users')->cascadeOnDelete();
                $table->text('mensaje');
                $table->timestamp('leido_at')->nullable();
                $table->timestamps();

                $table->index(['receptor_id', 'leido_at']);
                $table->index(['chat_conversacion_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_mensajes');
        Schema::dropIfExists('chat_conversaciones');
    }
};
