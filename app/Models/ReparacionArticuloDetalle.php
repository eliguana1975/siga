<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReparacionArticuloDetalle extends Model
{
    protected $table = 'reparacion_articulo_detalles';

    protected $fillable = [
        'reparacion_articulo_id',
        'articulo_id',
        'descripcion_articulo_manual',
        'codigo_articulo_manual',
        'cantidad_enviada',
        'cantidad_devuelta',
        'costo_unitario',
        'estado',
        'fecha_ultima_devolucion',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_enviada' => 'integer',
        'cantidad_devuelta' => 'integer',
        'costo_unitario' => 'decimal:2',
        'fecha_ultima_devolucion' => 'date',
    ];

    public function reparacion(): BelongsTo
    {
        return $this->belongsTo(ReparacionArticulo::class, 'reparacion_articulo_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }

    public function cantidadPendiente(): int
    {
        return max(0, (int) $this->cantidad_enviada - (int) $this->cantidad_devuelta);
    }

    public function nombreArticulo(): string
    {
        if ($this->articulo?->nombre) {
            return (string) $this->articulo->nombre;
        }

        return (string) ($this->descripcion_articulo_manual ?: 'N/A');
    }

    public function codigoArticulo(): string
    {
        if ($this->articulo?->codigo_producto) {
            return (string) $this->articulo->codigo_producto;
        }

        return (string) ($this->codigo_articulo_manual ?: '-');
    }

    public function refreshEstado($fechaCompromiso = null): void
    {
        $pendiente = $this->cantidadPendiente();

        if ($pendiente <= 0) {
            $estado = 'devuelta_total';
        } elseif ((int) $this->cantidad_devuelta > 0) {
            $estado = 'devuelta_parcial';
        } else {
            $estado = 'enviada';
        }

        if ($fechaCompromiso && now()->isAfter($fechaCompromiso) && $pendiente > 0) {
            $estado = 'vencida';
        }

        $this->forceFill(['estado' => $estado])->save();
    }
}
