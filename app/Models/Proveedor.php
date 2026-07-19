<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['provincia_id', 'ciudades_id', 'nombre', 'telefono', 'email', 'direccion', 'codigo_postal', 'contacto', 'forma_pago_preferida', 'condicion_pago_dias', 'datos_pago', 'impuestos', 'notas'])]
class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $casts = [
        'impuestos' => 'array',
    ];

    public const FORMAS_PAGO = [
        'transferencia' => 'Transferencia',
        'cheque' => 'Cheque',
        'e_check' => 'ECheq',
        'efectivo' => 'Efectivo',
        'cuenta_corriente' => 'Cuenta corriente',
        'tarjeta' => 'Tarjeta',
        'otro' => 'Otro',
    ];

    public static function formasPago(): array
    {
        return self::FORMAS_PAGO;
    }

    public function formaPagoPreferidaLabel(): string
    {
        return self::FORMAS_PAGO[$this->forma_pago_preferida] ?? 'Sin definir';
    }

    public function condicionPagoLabel(): string
    {
        if ($this->condicion_pago_dias === null || $this->condicion_pago_dias === '') {
            return 'Sin definir';
        }

        return $this->condicion_pago_dias === '0'
            ? 'A la vista'
            : str_replace('-', ' - ', $this->condicion_pago_dias) . ' dias';
    }

    public function impuestosActivos(): array
    {
        return collect($this->impuestos ?? [])
            ->filter(fn ($impuesto) => ! empty($impuesto['activo']) && trim((string) ($impuesto['nombre'] ?? '')) !== '')
            ->values()
            ->all();
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudades_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function compraDetalles()
    {
        return $this->hasMany(CompraDetalle::class, 'proveedor_id');
    }

    public function compraPagos()
    {
        return $this->hasMany(CompraPago::class, 'proveedor_id');
    }

}
