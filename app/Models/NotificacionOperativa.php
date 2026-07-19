<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionOperativa extends Model
{
    protected $table = 'notificaciones_operativas';

    protected $fillable = [
        'clave',
        'tipo',
        'severidad',
        'titulo',
        'mensaje',
        'url',
        'entidad_type',
        'entidad_id',
        'metadata',
        'first_seen_at',
        'last_seen_at',
        'read_at',
        'resolved_at',
        'resolved_by_user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
