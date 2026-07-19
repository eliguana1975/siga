<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'orden_trabajo_id',
    'articulo_id',
    'cantidad',
    'valor_unitario',
    'inventario_descontado',
    'numero_movimiento',
    'matafuego_numero',
    'matafuego_fecha_vencimiento',
    'observaciones',
])]
class OrdenTrabajoArticulo extends Model
{
    use HasFactory;

    protected $table = 'orden_trabajo_articulos';

    protected $casts = [
        'cantidad' => 'integer',
        'valor_unitario' => 'decimal:2',
        'inventario_descontado' => 'boolean',
        'matafuego_fecha_vencimiento' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (OrdenTrabajoArticulo $detalle) {
            if (blank($detalle->numero_movimiento)) {
                $detalle->forceFill([
                    'numero_movimiento' => self::numeroMovimientoPara($detalle),
                ])->saveQuietly();
            }
        });
    }

    public static function numeroMovimientoPara(self $detalle): string
    {
        $year = ($detalle->created_at ?? now())->format('Y');

        return 'MOV-' . $year . '-' . str_pad((string) $detalle->id, 6, '0', STR_PAD_LEFT);
    }

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class);
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }

    public function detalleCambioCubierta(): HasOne
    {
        return $this->hasOne(DetalleCambioCubierta::class);
    }
}
