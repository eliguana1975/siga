<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $fillable = [
        'deposito_id',
        'articulo_id',
        'precio_compra_unidad',
        'cantidad',
        'stock_minimo',
        'stock_maximo',
        'estado',
    ];

    protected $casts = [
        'precio_compra_unidad' => 'decimal:2',
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'fecha_registro' => 'datetime',
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_id');
    }

    public function cubiertas(): HasMany
    {
        return $this->hasMany(Cubierta::class, 'inventario_id');
    }

}
