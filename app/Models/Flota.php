<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\MarcaCarroceria;
use App\Models\MarcaVehiculo;
use App\Models\ModeloCaja;
use App\Models\ModeloMotor;
use App\Models\TipoCaja;
use App\Models\TipoMotor;
use App\Models\TipoVehiculo;
use App\Models\Titular;
use App\Models\CiaSeguro;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tipo_motor_id',
    'modelo_motor_id',
    'cod_marca_carroceria_id',
    'cod_titular_id',
    'marca_vehiculo_id',
    'cod_tipo_vehiculo_id',
    'cod_cia_seguro_id',
    'modelo_caja_id',
    'tipo_caja_id',
    'tipo_aceite_motor',
    'tipo_aceite_caja',
    'nro_interno',
    'dominio',
    'estado',
    'tipo_medidor_servicio',
    'horometro_actual',
    'servicio_asignado_actual_id',
    'nro_motor',
    'nro_chasis',
    'cant_aceite_motor',
    'cant_aceite_caja',
    'med_cub_delanteras',
    'med_cub_traseras',
    'cantidad_pasajeros',
    'anio_fabricacion',
    'nro_poliza',
    'estado_seguro',
    'observaciones',
    'foto_flota',
    'foto_flota_2',
    'foto_flota_3',
    'foto_flota_4',
])]
class Flota extends Model
{
    use HasFactory;

    protected $table = 'flota';

    public const TIPOS_MEDIDOR_SERVICIO = [
        'km' => 'Kilometros',
        'horas' => 'Horas',
    ];

    protected $casts = [
        'horometro_actual' => 'integer',
    ];

    public function tipoMotor()
    {
        return $this->belongsTo(TipoMotor::class, 'tipo_motor_id');
    }

    public function modeloMotor()
    {
        return $this->belongsTo(ModeloMotor::class, 'modelo_motor_id');
    }

    public function marcaCarroceria()
    {
        return $this->belongsTo(MarcaCarroceria::class, 'cod_marca_carroceria_id');
    }

    public function titular()
    {
        return $this->belongsTo(Titular::class, 'cod_titular_id');
    }

    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'cod_tipo_vehiculo_id');
    }

    public function ciaSeguro()
    {
        return $this->belongsTo(CiaSeguro::class, 'cod_cia_seguro_id');
    }

    public function modeloCaja()
    {
        return $this->belongsTo(ModeloCaja::class, 'modelo_caja_id');
    }

    public function tipoCaja()
    {
        return $this->belongsTo(TipoCaja::class, 'tipo_caja_id');
    }

    public function marcaVehiculo()
    {
        return $this->belongsTo(MarcaVehiculo::class, 'marca_vehiculo_id');
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class);
    }

    public function verificacionesTecnicas(): HasMany
    {
        return $this->hasMany(RegistroVerificacionTecnica::class);
    }

    public function servicioAsignadoActual()
    {
        return $this->belongsTo(ServicioAsignado::class, 'servicio_asignado_actual_id');
    }

    public function historialServiciosAsignados(): HasMany
    {
        return $this->hasMany(FlotaServicioAsignadoHistorial::class);
    }

    public function repuestos(): HasMany
    {
        return $this->hasMany(FlotaRepuesto::class);
    }

    public function cubiertaEjes(): HasMany
    {
        return $this->hasMany(FlotaCubiertaEje::class)->where('activo', true)->orderBy('orden')->orderBy('numero_eje');
    }

    public function cubiertaLayout(): array
    {
        $ejes = $this->relationLoaded('cubiertaEjes')
            ? $this->cubiertaEjes
            : $this->cubiertaEjes()->get();

        if ($ejes->isEmpty()) {
            return self::defaultCubiertaLayout();
        }

        return $ejes
            ->map(function ($eje, int $index) {
                $numero = (int) ($eje->numero_eje ?? ($index + 1));
                $izquierda = max(0, (int) ($eje->cubiertas_izquierda ?? 1));
                $derecha = max(0, (int) ($eje->cubiertas_derecha ?? 1));

                $articuloCubiertaId = $eje->articulo_cubierta_id ? (int) $eje->articulo_cubierta_id : null;
                $posicionesIzquierda = $this->cubiertaPosicionesLado($numero, (string) ($eje->tipo_eje ?? 'trasero'), 'I', $izquierda);
                $posicionesDerecha = $this->cubiertaPosicionesLado($numero, (string) ($eje->tipo_eje ?? 'trasero'), 'D', $derecha);

                if ($articuloCubiertaId) {
                    $posicionesIzquierda = array_map(fn (array $posicion) => $posicion + ['articulo_cubierta_id' => $articuloCubiertaId], $posicionesIzquierda);
                    $posicionesDerecha = array_map(fn (array $posicion) => $posicion + ['articulo_cubierta_id' => $articuloCubiertaId], $posicionesDerecha);
                }

                return [
                    'numero_eje' => $numero,
                    'tipo_eje' => (string) ($eje->tipo_eje ?? 'trasero'),
                    'tipo_label' => FlotaCubiertaEje::TIPOS[$eje->tipo_eje ?? 'trasero'] ?? ucfirst((string) ($eje->tipo_eje ?? 'trasero')),
                    'articulo_cubierta_id' => $articuloCubiertaId,
                    'cubiertas_izquierda' => $izquierda,
                    'cubiertas_derecha' => $derecha,
                    'posiciones_izquierda' => $posicionesIzquierda,
                    'posiciones_derecha' => $posicionesDerecha,
                ];
            })
            ->values()
            ->all();
    }

    public function cubiertaPosiciones(): array
    {
        return collect($this->cubiertaLayout())
            ->flatMap(fn (array $eje) => array_merge($eje['posiciones_izquierda'], $eje['posiciones_derecha']))
            ->values()
            ->all();
    }

    public static function defaultCubiertaEjes(): array
    {
        return [
            (object) [
                'numero_eje' => 1,
                'tipo_eje' => 'delantero',
                'cubiertas_izquierda' => 1,
                'cubiertas_derecha' => 1,
            ],
            (object) [
                'numero_eje' => 2,
                'tipo_eje' => 'trasero',
                'cubiertas_izquierda' => 2,
                'cubiertas_derecha' => 2,
            ],
        ];
    }

    public static function defaultCubiertaLayout(): array
    {
        return [
            [
                'numero_eje' => 1,
                'tipo_eje' => 'delantero',
                'tipo_label' => 'Delantero',
                'cubiertas_izquierda' => 1,
                'cubiertas_derecha' => 1,
                'posiciones_izquierda' => [
                    ['codigo' => 'DI', 'etiqueta' => 'DI', 'lado' => 'I', 'orden' => 1, 'numero_eje' => 1],
                ],
                'posiciones_derecha' => [
                    ['codigo' => 'DD', 'etiqueta' => 'DD', 'lado' => 'D', 'orden' => 1, 'numero_eje' => 1],
                ],
            ],
            [
                'numero_eje' => 2,
                'tipo_eje' => 'trasero',
                'tipo_label' => 'Trasero',
                'cubiertas_izquierda' => 2,
                'cubiertas_derecha' => 2,
                'posiciones_izquierda' => [
                    ['codigo' => 'TIE', 'etiqueta' => 'TIE', 'lado' => 'I', 'orden' => 1, 'numero_eje' => 2],
                    ['codigo' => 'TII', 'etiqueta' => 'TII', 'lado' => 'I', 'orden' => 2, 'numero_eje' => 2],
                ],
                'posiciones_derecha' => [
                    ['codigo' => 'TDI', 'etiqueta' => 'TDI', 'lado' => 'D', 'orden' => 1, 'numero_eje' => 2],
                    ['codigo' => 'TDE', 'etiqueta' => 'TDE', 'lado' => 'D', 'orden' => 2, 'numero_eje' => 2],
                ],
            ],
        ];
    }

    private function cubiertaPosicionesLado(int $numeroEje, string $tipoEje, string $lado, int $cantidad): array
    {
        return collect(range(1, max(1, $cantidad)))
            ->take($cantidad)
            ->map(function (int $orden) use ($numeroEje, $tipoEje, $lado, $cantidad) {
                $codigo = $this->cubiertaCodigoPosicion($numeroEje, $tipoEje, $lado, $orden, $cantidad);

                return [
                    'codigo' => $codigo,
                    'etiqueta' => $codigo,
                    'lado' => $lado,
                    'orden' => $orden,
                    'numero_eje' => $numeroEje,
                ];
            })
            ->all();
    }

    private function cubiertaCodigoPosicion(int $numeroEje, string $tipoEje, string $lado, int $orden, int $cantidad): string
    {
        if ($tipoEje === 'auxiliar') {
            $suffix = $numeroEje > 1 ? (string) $numeroEje : '';

            if ($cantidad === 1) {
                return $lado === 'I' ? "AUXI{$suffix}" : "AUXD{$suffix}";
            }

            return $lado === 'I'
                ? 'AUXI' . $suffix . ($orden > 1 ? "-{$orden}" : '')
                : 'AUXD' . $suffix . ($orden > 1 ? "-{$orden}" : '');
        }

        if ($tipoEje === 'delantero') {
            if ($cantidad === 1) {
                return $lado === 'I' ? 'DI' : 'DD';
            }

            return $lado === 'I'
                ? ($orden === 1 ? 'DIE' : ($orden === 2 ? 'DII' : "DI{$orden}"))
                : ($orden === 1 ? 'DDI' : ($orden === 2 ? 'DDE' : "DD{$orden}"));
        }

        if ($tipoEje === 'trasero' || $tipoEje === 'acoplado') {
            $suffix = $numeroEje > 2 ? (string) $numeroEje : '';

            if ($cantidad === 1) {
                return $lado === 'I' ? "TIE{$suffix}" : "TDI{$suffix}";
            }

            return $lado === 'I'
                ? ($orden === 1 ? "TIE{$suffix}" : ($orden === 2 ? "TII{$suffix}" : "TI{$suffix}-{$orden}"))
                : ($orden === 1 ? "TDI{$suffix}" : ($orden === 2 ? "TDE{$suffix}" : "TD{$suffix}-{$orden}"));
        }

        if ($cantidad === 1) {
            return "E{$numeroEje}{$lado}";
        }

        return $lado === 'I'
            ? "E{$numeroEje}" . ($orden === 1 ? 'IE' : ($orden === 2 ? 'II' : "I{$orden}"))
            : "E{$numeroEje}" . ($orden === 1 ? 'DI' : ($orden === 2 ? 'DE' : "D{$orden}"));
    }
}
