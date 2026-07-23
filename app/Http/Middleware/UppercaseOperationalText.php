<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UppercaseOperationalText
{
    private const FIELD_NAMES = [
        'apellido',
        'asunto',
        'barrio',
        'calle',
        'cargo',
        'ciudad',
        'codigo',
        'codigo_producto',
        'descripcion',
        'detalle',
        'direccion',
        'domicilio',
        'dominio',
        'localidad',
        'marca',
        'modelo',
        'motivo',
        'name',
        'nombre',
        'nro_chasis',
        'nro_interno',
        'nro_motor',
        'nro_poliza',
        'observaciones',
        'provincia',
        'razon_social',
        'referencia',
        'ubicacion',
    ];

    private const FIELD_SUFFIXES = [
        '_descripcion',
        '_detalle',
        '_direccion',
        '_domicilio',
        '_dominio',
        '_nombre',
        '_observaciones',
        '_referencia',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe() && $request->is('admin/*')) {
            $request->merge($this->uppercaseData($request->all()));
        }

        return $next($request);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function uppercaseData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->uppercaseData($value);
                continue;
            }

            if (is_string($key) && is_string($value) && $this->shouldUppercase($key)) {
                $data[$key] = mb_strtoupper(trim($value), 'UTF-8');
            }
        }

        return $data;
    }

    private function shouldUppercase(string $key): bool
    {
        $normalized = mb_strtolower($key, 'UTF-8');

        if (in_array($normalized, self::FIELD_NAMES, true)) {
            return true;
        }

        foreach (self::FIELD_SUFFIXES as $suffix) {
            if (str_ends_with($normalized, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
