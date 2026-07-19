<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraPago extends Model
{
    protected $table = 'compra_pagos';

    protected $fillable = [
        'compra_id',
        'proveedor_id',
        'usuario_id',
        'forma_pago',
        'tipo_pago',
        'porcentaje_pago',
        'importe',
        'importe_base',
        'importe_impuestos',
        'impuestos_aplicados',
        'fecha_pago',
        'nro_cheque',
        'nros_cheques',
        'tipo_cheque',
        'banco_id',
        'banco',
        'titular_cheque',
        'cuit_librador',
        'nro_cuenta_cheque',
        'nro_operacion_cheque',
        'fecha_emision_cheque',
        'fecha_vencimiento_cheque',
        'plazo_pago',
        'vencimientos_pago',
        'nro_comprobante_pago',
        'nro_transferencia',
        'nro_recibo',
        'fecha_comprobante_pago',
        'observaciones',
        'observaciones_comprobante',
    ];

    protected $casts = [
        'importe' => 'decimal:2',
        'porcentaje_pago' => 'decimal:4',
        'importe_base' => 'decimal:2',
        'importe_impuestos' => 'decimal:2',
        'impuestos_aplicados' => 'array',
        'fecha_pago' => 'date',
        'nros_cheques' => 'array',
        'fecha_emision_cheque' => 'date',
        'fecha_vencimiento_cheque' => 'date',
        'fecha_comprobante_pago' => 'date',
        'vencimientos_pago' => 'array',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function bancoSeleccionado(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function formaPagoLabel(): string
    {
        return Compra::FORMAS_PAGO[$this->forma_pago] ?? ucfirst((string) $this->forma_pago);
    }

    public function vencimientosPago(): array
    {
        return collect($this->vencimientos_pago ?? [])
            ->filter()
            ->values()
            ->all();
    }

    public function numerosCheques(): array
    {
        $numeros = collect($this->nros_cheques ?? [])
            ->filter()
            ->values()
            ->all();

        if (! empty($numeros)) {
            return $numeros;
        }

        return filled($this->nro_cheque) ? [$this->nro_cheque] : [];
    }

    public function impuestosAplicados(): array
    {
        return collect($this->impuestos_aplicados ?? [])
            ->filter()
            ->values()
            ->all();
    }

    public function tieneComprobante(): bool
    {
        return filled($this->nro_comprobante_pago)
            || filled($this->nro_recibo)
            || filled($this->nro_transferencia)
            || filled($this->fecha_comprobante_pago);
    }
}
