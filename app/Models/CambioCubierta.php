<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CambioCubierta extends Model
{
    use HasFactory;

    protected $table = 'cambios_cubiertas';

    protected $fillable = [
        'orden_trabajo_id',
        'flota_id',
        'empleado_id',
        'user_id',
        'fecha',
        'kilometraje',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'kilometraje' => 'integer',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class);
    }

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCambioCubierta::class);
    }
}
