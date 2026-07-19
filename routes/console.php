<?php

use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:prune-old-messages', function () {
    $cutoff = now()->subDays(90);
    $conversationIds = ChatMensaje::query()
        ->where('created_at', '<', $cutoff)
        ->distinct()
        ->pluck('chat_conversacion_id');

    if ($conversationIds->isEmpty()) {
        $this->info('No hay mensajes de chat interno para borrar.');

        return 0;
    }

    $deleted = 0;

    DB::transaction(function () use ($cutoff, $conversationIds, &$deleted) {
        $deleted = ChatMensaje::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        foreach ($conversationIds as $conversationId) {
            $lastMessageAt = ChatMensaje::query()
                ->where('chat_conversacion_id', $conversationId)
                ->max('created_at');

            ChatConversacion::whereKey($conversationId)
                ->update(['last_message_at' => $lastMessageAt]);
        }
    });

    $this->info("Mensajes de chat interno eliminados: {$deleted}.");

    return 0;
})->purpose('Borra automaticamente mensajes del chat interno con mas de 90 dias');
