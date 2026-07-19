<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReparacionArticuloReclamo extends Model
{
    protected $table = 'reparacion_articulo_reclamos';

    protected $fillable = [
        'reparacion_articulo_id',
        'reparacion_articulo_detalle_id',
        'fecha_reclamo',
        'medio',
        'numero_referencia',
        'observaciones',
        'respuesta_proveedor',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_reclamo' => 'date',
    ];

    public function reparacion(): BelongsTo
    {
        return $this->belongsTo(ReparacionArticulo::class, 'reparacion_articulo_id');
    }

    public function detalle(): BelongsTo
    {
        return $this->belongsTo(ReparacionArticuloDetalle::class, 'reparacion_articulo_detalle_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
