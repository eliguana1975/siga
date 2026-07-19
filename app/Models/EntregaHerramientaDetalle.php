<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaHerramientaDetalle extends Model
{
    protected $table = 'entrega_herramienta_detalles';

    protected $fillable = [
        'entrega_herramienta_id',
        'articulo_id',
        'cantidad_entregada',
        'cantidad_devuelta',
        'estado',
        'condicion_entrega',
        'condicion_devolucion',
        'fecha_devolucion',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_entregada' => 'integer',
        'cantidad_devuelta' => 'integer',
        'fecha_devolucion' => 'date',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(EntregaHerramienta::class, 'entrega_herramienta_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }

    public function cantidadPendiente(): int
    {
        return max(0, (int) $this->cantidad_entregada - (int) $this->cantidad_devuelta);
    }
}
