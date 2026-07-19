<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrada extends Model
{
    protected $table = 'entrada';

    protected $fillable = [
        'compra_id',
        'deposito_id',
        'proveedor_id',
        'usuario_id',
        'nro_orden_compra',
        'nro_comprobante_proveedor',
        'fecha_entrada',
        'total_entrada',
        'observaciones',
    ];

    protected $casts = [
        'fecha_entrada' => 'datetime',
        'total_entrada' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleEntrada::class, 'entrada_id');
    }
}
