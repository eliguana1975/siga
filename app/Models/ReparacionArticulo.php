<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReparacionArticulo extends Model
{
    protected $table = 'reparaciones_articulos';

    protected $fillable = [
        'numero_orden',
        'proveedor_id',
        'provincia_id',
        'ciudad_id',
        'quien_envia_nombre',
        'quien_envia_documento',
        'quien_recibe_nombre',
        'quien_recibe_documento',
        'domicilio',
        'telefono',
        'codigo_postal',
        'fecha_envio',
        'fecha_compromiso',
        'estado',
        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_envio' => 'date',
        'fecha_compromiso' => 'date',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ReparacionArticuloDetalle::class, 'reparacion_articulo_id');
    }

    public function reclamos(): HasMany
    {
        return $this->hasMany(ReparacionArticuloReclamo::class, 'reparacion_articulo_id');
    }

    public function cantidadPendienteTotal(): int
    {
        $this->loadMissing('detalles');

        return (int) $this->detalles->sum(fn (ReparacionArticuloDetalle $detalle) => $detalle->cantidadPendiente());
    }

    public function refreshEstado(): void
    {
        $this->loadMissing('detalles');

        if ($this->detalles->isEmpty()) {
            $this->forceFill(['estado' => 'cancelada'])->save();

            return;
        }

        $cantidadEnviada = (int) $this->detalles->sum('cantidad_enviada');
        $cantidadPendiente = $this->cantidadPendienteTotal();

        foreach ($this->detalles as $detalle) {
            $detalle->refreshEstado($this->fecha_compromiso);
        }

        if ($cantidadPendiente <= 0) {
            $estado = 'completada';
        } elseif ($cantidadPendiente < $cantidadEnviada) {
            $estado = 'parcial';
        } else {
            $estado = 'enviada';
        }

        $fechaCompromiso = $this->fecha_compromiso ? strtotime((string) $this->fecha_compromiso) : null;
        $hoy = strtotime(now()->toDateString());

        if ($fechaCompromiso && $fechaCompromiso < $hoy && $cantidadPendiente > 0) {
            $estado = 'vencida';
        }

        $this->forceFill(['estado' => $estado])->save();
    }
}
