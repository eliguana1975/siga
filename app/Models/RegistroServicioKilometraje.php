<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroServicioKilometraje extends Model
{
    protected $table = 'registros_servicios_kilometraje';

    protected $fillable = [
        'flota_id',
        'configuracion_intervalo_servicio_id',
        'user_id',
        'kilometraje_servicio',
        'horometro_servicio',
        'fecha_servicio',
        'observaciones',
    ];

    protected $casts = [
        'fecha_servicio' => 'datetime',
        'kilometraje_servicio' => 'integer',
        'horometro_servicio' => 'integer',
    ];

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function intervalo(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionIntervaloServicio::class, 'configuracion_intervalo_servicio_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
