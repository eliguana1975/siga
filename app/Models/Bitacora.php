<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'usuario_nombre',
    'accion',
    'modulo',
    'entidad_type',
    'entidad_id',
    'descripcion',
    'datos_anteriores',
    'datos_nuevos',
    'metadata',
    'ip_address',
    'user_agent',
    'url',
    'method',
    'route_name',
])]
class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacoras';

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
            'metadata' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
