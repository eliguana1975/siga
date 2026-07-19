<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlotaCubiertaEje extends Model
{
    protected $table = 'flota_cubierta_ejes';

    protected $fillable = [
        'flota_id',
        'numero_eje',
        'tipo_eje',
        'articulo_cubierta_id',
        'cubiertas_izquierda',
        'cubiertas_derecha',
        'orden',
        'activo',
    ];

    protected $casts = [
        'numero_eje' => 'integer',
        'cubiertas_izquierda' => 'integer',
        'cubiertas_derecha' => 'integer',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    public const TIPOS = [
        'delantero' => 'Delantero',
        'trasero' => 'Trasero',
        'auxiliar' => 'Auxiliar',
        'acoplado' => 'Acoplado',
    ];

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function articuloCubierta(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_cubierta_id');
    }
}
