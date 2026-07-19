<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaDepositoDetalle extends Model
{
    protected $table = 'transferencia_deposito_detalles';

    protected $fillable = [
        'transferencia_id',
        'inventario_origen_id',
        'articulo_id',
        'cantidad',
        'precio_compra_unidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_compra_unidad' => 'decimal:2',
    ];

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(TransferenciaDeposito::class, 'transferencia_id');
    }

    public function inventarioOrigen(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'inventario_origen_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
}
