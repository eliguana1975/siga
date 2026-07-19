<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'flota_id',
    'servicio_asignado_id',
    'fecha_desde',
    'fecha_hasta',
    'observaciones',
])]
class FlotaServicioAsignadoHistorial extends Model
{
    use HasFactory;

    protected $table = 'flota_servicio_asignado_historial';

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
        ];
    }

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function servicioAsignado(): BelongsTo
    {
        return $this->belongsTo(ServicioAsignado::class);
    }
}
