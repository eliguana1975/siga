<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCambioCubierta extends Model
{
    use HasFactory;

    protected $table = 'detalle_cambio_cubiertas';

    protected $fillable = [
        'cambio_cubierta_id',
        'articulo_colocado_id',
        'cubierta_colocada_id',
        'cubierta_sacada_id',
        'orden_trabajo_articulo_id',
        'posicion',
        'nro_cubierta_sacada',
        'estado_cubierta_sacada',
        'destino_cubierta_sacada',
        'motivo_baja_cubierta_sacada',
        'observacion_cubierta_sacada',
        'nro_cubierta_colocada',
        'valor_unitario',
    ];

    public const ESTADOS_CUBIERTA_SACADA = [
        'buena' => 'Buena para reutilizar',
        'revisar' => 'Revisar',
        'reparar' => 'Reparar',
        'baja' => 'Baja definitiva',
    ];

    public const DESTINOS_CUBIERTA_SACADA = [
        'deposito' => 'Deposito',
        'gomeria' => 'Gomeria / reparacion',
        'descarte' => 'Descarte',
        'garantia' => 'Garantia',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
    ];

    public function cambioCubierta(): BelongsTo
    {
        return $this->belongsTo(CambioCubierta::class);
    }

    public function articuloColocado(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_colocado_id');
    }

    public function cubiertaColocada(): BelongsTo
    {
        return $this->belongsTo(Cubierta::class, 'cubierta_colocada_id');
    }

    public function cubiertaSacada(): BelongsTo
    {
        return $this->belongsTo(Cubierta::class, 'cubierta_sacada_id');
    }

    public function ordenTrabajoArticulo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajoArticulo::class);
    }

    public function estadoCubiertaSacadaLabel(): string
    {
        return self::ESTADOS_CUBIERTA_SACADA[$this->estado_cubierta_sacada] ?? 'Sin evaluar';
    }

    public function destinoCubiertaSacadaLabel(): string
    {
        return self::DESTINOS_CUBIERTA_SACADA[$this->destino_cubierta_sacada] ?? '-';
    }
}
