<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    private ?float $importeImpuestosProveedorCache = null;

    protected $fillable = [
        'deposito_id',
        'proveedor_id',
        'pedido_articulo_id',
        'usuario_id',
        'fecha_compra',
        'total_compra',
        'estado',
        'comprobante',
        'forma_pago',
        'datos_pago',
        'notas',
    ];

    protected $casts = [
        'fecha_compra' => 'datetime',
        'total_compra' => 'decimal:2',
    ];

    public const FORMAS_PAGO = Proveedor::FORMAS_PAGO;

    public static function formasPago(): array
    {
        return self::FORMAS_PAGO;
    }

    public function formaPagoLabel(): string
    {
        return self::FORMAS_PAGO[$this->forma_pago] ?? 'Sin definir';
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function pedidoArticulo(): BelongsTo
    {
        return $this->belongsTo(PedidoArticulo::class, 'pedido_articulo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(CompraPago::class);
    }

    public function totalPagado(): float
    {
        if ($this->relationLoaded('pagos')) {
            return (float) $this->pagos->sum('importe');
        }

        return (float) $this->pagos()->sum('importe');
    }

    public function saldoPendiente(): float
    {
        return max(0, (float) $this->total_compra - $this->totalPagado());
    }

    public function importeImpuestosProveedor(): float
    {
        if ($this->importeImpuestosProveedorCache !== null) {
            return $this->importeImpuestosProveedorCache;
        }

        $detalles = $this->relationLoaded('detalles')
            ? $this->detalles
            : $this->detalles()->with('proveedor')->get();

        $this->importeImpuestosProveedorCache = (float) $detalles->sum(function (CompraDetalle $detalle) {
            $subtotal = (float) $detalle->precio_compra_unidad * (int) $detalle->cantidad;
            $proveedor = $detalle->proveedor ?: $this->proveedor;
            $porcentaje = collect($proveedor?->impuestosActivos() ?? [])
                ->sum(fn ($impuesto) => (float) ($impuesto['porcentaje'] ?? 0));

            return $subtotal * $porcentaje / 100;
        });

        return $this->importeImpuestosProveedorCache;
    }

    public function totalConImpuestos(): float
    {
        return (float) $this->total_compra + $this->importeImpuestosProveedor();
    }

    public function saldoPendienteConImpuestos(): float
    {
        return max(0, $this->totalConImpuestos() - $this->totalPagado());
    }

    public function estadoPagoResumen(): array
    {
        $totalPagado = $this->totalPagado();

        if ($totalPagado <= 0) {
            return ['label' => 'Pago pendiente', 'class' => 'bg-light-danger'];
        }

        if ($this->saldoPendienteConImpuestos() > 0) {
            return ['label' => 'Pago parcial', 'class' => 'bg-light-warning'];
        }

        return ['label' => 'Pagada', 'class' => 'bg-light-success'];
    }

    public function proveedorResumen(): string
    {
        $proveedores = $this->relationLoaded('detalles')
            ? $this->detalles->pluck('proveedor.nombre')
            : $this->detalles()->with('proveedor')->get()->pluck('proveedor.nombre');

        $proveedores = $proveedores
            ->filter()
            ->unique()
            ->values();

        if ($proveedores->count() > 1) {
            return 'Varios proveedores';
        }

        if ($proveedores->count() === 1) {
            return (string) $proveedores->first();
        }

        return $this->proveedor?->nombre ?? 'Sin proveedor';
    }
}
