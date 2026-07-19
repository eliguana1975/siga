<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoArticulo extends Model
{
    public const STOCK_BYPASS_MARKER = '[EXCEPCION_STOCK_JEFE_COMPRAS]';

    protected $table = 'pedidos_articulo';

    protected $fillable = [
        'deposito_id',
        'usuario_id',
        'fecha_pedido',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
    ];

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalleArticulo::class, 'pedidos_articulo_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'pedido_articulo_id');
    }

    public function hasStockBypassException(): bool
    {
        return str_contains((string) ($this->notas ?? ''), self::STOCK_BYPASS_MARKER);
    }

    public function notasSinMarcadores(): ?string
    {
        $notas = trim(str_replace(self::STOCK_BYPASS_MARKER, '', (string) ($this->notas ?? '')));

        return $notas === '' ? null : $notas;
    }
}
