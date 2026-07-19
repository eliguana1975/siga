<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlotaRepuesto extends Model
{
    protected $table = 'flota_repuestos';

    protected $fillable = [
        'flota_id',
        'articulo_id',
        'configuracion_intervalo_servicio_id',
        'cantidad_servicio',
        'modo_carga_servicio',
        'obligatorio_servicio',
        'nombre_repuesto',
        'codigo_referencia',
        'marca',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'cantidad_servicio' => 'integer',
        'obligatorio_servicio' => 'boolean',
    ];

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class, 'flota_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function configuracionIntervaloServicio(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionIntervaloServicio::class, 'configuracion_intervalo_servicio_id');
    }
}
