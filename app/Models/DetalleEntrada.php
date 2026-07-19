<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleEntrada extends Model
{
    protected $table = 'detalle_entrada';

    protected $fillable = [
        'entrada_id',
        'compra_detalle_id',
        'articulo_id',
        'cantidad',
        'precio_compra_unidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_compra_unidad' => 'decimal:2',
    ];

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'entrada_id');
    }

    public function compraDetalle(): BelongsTo
    {
        return $this->belongsTo(CompraDetalle::class, 'compra_detalle_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }
}
