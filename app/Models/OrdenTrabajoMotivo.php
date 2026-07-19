<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'codigo',
    'nombre',
    'activo',
])]
class OrdenTrabajoMotivo extends Model
{
    use HasFactory;

    protected $table = 'orden_trabajo_motivos';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function ordenesTrabajo(): BelongsToMany
    {
        return $this->belongsToMany(
            OrdenTrabajo::class,
            'orden_trabajo_motivo',
            'orden_trabajo_motivo_id',
            'orden_trabajo_id'
        )->withTimestamps();
    }
}
