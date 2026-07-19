<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cubierta extends Model
{
    protected $fillable = [
        'articulo_id',
        'inventario_id',
        'deposito_id',
        'entrada_id',
        'detalle_entrada_id',
        'flota_id',
        'posicion',
        'medida',
        'secuencia',
        'numero',
        'estado',
        'fecha_ingreso',
        'fecha_baja',
        'observaciones',
    ];

    protected $casts = [
        'secuencia' => 'integer',
        'fecha_ingreso' => 'date',
        'fecha_baja' => 'date',
    ];

    public const ESTADOS = [
        'nueva' => 'Nueva',
        'reutilizable' => 'Reutilizable',
        'en_uso' => 'En uso',
        'baja' => 'Baja',
    ];

    public static function medidaDesdeArticulo(Articulo $articulo): string
    {
        return trim((string) ($articulo->codigo_producto ?: $articulo->nombre));
    }

    public static function numeroPara(Articulo $articulo, int $secuencia): string
    {
        return (string) $secuencia;
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class);
    }

    public function detalleEntrada(): BelongsTo
    {
        return $this->belongsTo(DetalleEntrada::class);
    }

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }
}
