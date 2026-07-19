<?php

namespace App\Support;

use App\Models\Ajuste;
use App\Models\Titular;

class CompanyTitularSync
{
    public function sync(?Ajuste $ajuste = null): ?Titular
    {
        $ajuste ??= Ajuste::query()->first();

        if (! $ajuste || blank($ajuste->nombre)) {
            return null;
        }

        $data = [
            'nombre' => mb_strtoupper(trim((string) $ajuste->nombre), 'UTF-8'),
            'direccion' => trim((string) ($ajuste->direccion ?? '')),
            'telefono' => trim((string) ($ajuste->telefono ?? '')),
            'email' => trim((string) ($ajuste->email ?? '')),
            'es_empresa' => true,
        ];

        $titular = Titular::query()
            ->where('es_empresa', true)
            ->first();

        if (! $titular) {
            $titular = Titular::query()
                ->whereRaw('UPPER(nombre) = ?', [$data['nombre']])
                ->first();
        }

        if ($titular) {
            $titular->fill($data)->save();

            return $titular;
        }

        return Titular::query()->create($data);
    }
}
