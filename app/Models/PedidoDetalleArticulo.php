<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoDetalleArticulo extends Model
{
    protected $table = 'pedido_detalle_articulo';

    protected $fillable = [
        'pedidos_articulo_id',
        'articulo_id',
        'cantidad',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoArticulo::class, 'pedidos_articulo_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }
}
