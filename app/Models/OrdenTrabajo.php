<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'empleado_id',
    'actualizado_por_user_id',
    'reparador_empleado_id',
    'flota_id',
    'base_id',
    'kilometraje',
    'fecha_orden',
    'tipo_trabajo',
    'prioridad',
    'estado',
    'vehiculo_parado',
    'motivo_vehiculo_parado',
    'fecha_vehiculo_parado',
    'observacion_vehiculo_parado',
    'titulo',
    'descripcion',
    'observaciones',
    'fecha_cierre',
])]
class OrdenTrabajo extends Model
{
    use HasFactory;

    protected $table = 'ordenes_trabajo';

    protected function casts(): array
    {
        return [
            'fecha_orden' => 'datetime',
            'fecha_cierre' => 'datetime',
            'vehiculo_parado' => 'boolean',
            'fecha_vehiculo_parado' => 'date',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por_user_id');
    }

    public function reparador(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'reparador_empleado_id');
    }

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    public function articulosUsados(): HasMany
    {
        return $this->hasMany(OrdenTrabajoArticulo::class);
    }

    public function motivos(): BelongsToMany
    {
        return $this->belongsToMany(
            OrdenTrabajoMotivo::class,
            'orden_trabajo_motivo',
            'orden_trabajo_id',
            'orden_trabajo_motivo_id'
        )->withTimestamps();
    }

    public function estaCerrada(): bool
    {
        return in_array($this->estado, ['completada', 'cancelada'], true);
    }
}
