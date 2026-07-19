<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversacion extends Model
{
    protected $table = 'chat_conversaciones';

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatMensaje::class, 'chat_conversacion_id');
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(ChatMensaje::class, 'chat_conversacion_id')->latestOfMany();
    }

    public function otroUsuario(int $userId): ?User
    {
        return (int) $this->user_one_id === $userId ? $this->userTwo : $this->userOne;
    }

    public static function between(int $firstUserId, int $secondUserId): ?self
    {
        [$userOneId, $userTwoId] = self::orderedUsers($firstUserId, $secondUserId);

        return self::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->first();
    }

    public static function firstOrCreateBetween(int $firstUserId, int $secondUserId): self
    {
        [$userOneId, $userTwoId] = self::orderedUsers($firstUserId, $secondUserId);

        return self::firstOrCreate([
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
        ]);
    }

    private static function orderedUsers(int $firstUserId, int $secondUserId): array
    {
        return $firstUserId < $secondUserId
            ? [$firstUserId, $secondUserId]
            : [$secondUserId, $firstUserId];
    }
}
