<?php

namespace App\Support;

use App\Models\Compra;

class CompraImpuestos
{
    public static function disponiblesParaPago(Compra $compra): array
    {
        $proveedor = $compra->proveedor ?: $compra->detalles->pluck('proveedor')->filter()->unique('id')->first();

        return collect($proveedor?->impuestosActivos() ?? [])
            ->map(fn ($impuesto) => [
                'nombre' => trim((string) $impuesto['nombre']),
                'porcentaje' => (float) ($impuesto['porcentaje'] ?? 0),
                'descripcion' => trim((string) ($impuesto['descripcion'] ?? '')),
                'origen' => 'Proveedor',
            ])
            ->values()
            ->all();
    }
}
