<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'flota_id',
    'conductor_user_id',
    'servicio_asignado_id',
    'orden_trabajo_id',
    'interno',
    'conductor',
    'kilometraje_actual',
    'servicio_asignado',
    'observaciones_generales',
    'partes',
    'control_unidad',
])]
class ControlUnidad extends Model
{
    use HasFactory;

    protected $table = 'controles_unidad';

    public const ESTADOS_PARTE = ['cumple', 'no_cumple', 'na'];
    public const ESTADOS_CONTROL = ['hecho', 'sin_hacer'];

    public const PARTES = [
        'documentacion' => [
            'titulo' => 'Documentacion',
            'items' => [
                'licencia_conducir_vigente' => 'Licencia de conducir vigente',
                'manejo_defensivo_vigente' => 'Manejo defensivo vigente',
                'vtv_vigente' => 'VTV vigente',
                'credencial_circulacion_vigente' => 'Credencial de circulacion vigente',
            ],
        ],
        'mecanica' => [
            'titulo' => 'Parte mecanica',
            'items' => [
                'frenos_delanteros' => 'Frenos delanteros',
                'frenos_traseros' => 'Frenos traseros',
                'freno_mano' => 'Freno de mano',
                'direccion' => 'Direccion',
                'suspension' => 'Suspension',
                'motor' => 'Motor',
                'caja' => 'Caja',
                'diferencial' => 'Diferencial',
                'circuito_aire' => 'Circuito de aire',
            ],
        ],
        'electricidad' => [
            'titulo' => 'Parte de electricidad',
            'items' => [
                'luz_posicion' => 'Luz posicion',
                'luz_giro_baliza' => 'Luz giro/baliza',
                'luz_alta' => 'Luz alta',
                'luz_baja' => 'Luz baja',
                'bocina' => 'Bocina',
                'limpia_parabrisas' => 'Limpia parabrisas',
                'sirena_retroceso' => 'Sirena de retroceso',
                'instrumental' => 'Instrumental',
                'audio' => 'Audio',
                'aire_acondicionado' => 'Aire acondicionado',
                'calefaccion' => 'Calefaccion',
            ],
        ],
        'gomeria' => [
            'titulo' => 'Parte de gomeria',
            'items' => [
                'cubiertas_delanteras' => 'Cubiertas delanteras',
                'cubiertas_traseras' => 'Cubiertas traseras',
                'auxilio' => 'Auxilio',
                'checkpoint' => 'Checkpoint',
            ],
        ],
        'carroceria' => [
            'titulo' => 'Parte de carroceria',
            'items' => [
                'parabrisas' => 'Parabrisas',
                'ventanillas_laterales' => 'Ventanillas laterales',
                'luneta_trasera' => 'Luneta trasera',
                'puerta_acceso' => 'Puerta de acceso',
                'butacas' => 'Butacas',
                'cinturones_seguridad' => 'Cinturones de seguridad',
                'matafuegos' => 'Matafuegos',
                'martillos' => 'Martillos',
                'cortinas' => 'Cortinas',
                'chapa' => 'Chapa',
                'espejos_retrovisores' => 'Espejos retrovisores',
                'porton_trasero' => 'Porton trasero',
            ],
        ],
        'accesorios' => [
            'titulo' => 'Accesorios',
            'items' => [
                'criquet' => 'Criquet',
                'llave_rueda' => 'Llave de rueda',
                'chalecos_reflectivos' => 'Chalecos reflectivos',
                'linterna' => 'Linterna',
                'casco' => 'Casco',
                'gafa' => 'Gafa',
                'zapatos_seguridad' => 'Zapatos de seguridad',
                'conos' => 'Conos',
                'calces_ruedas' => 'Calces de ruedas',
            ],
        ],
    ];

    public const CONTROL_UNIDAD = [
        'control_bateria_limpieza_bornes' => 'Control bateria: limpieza de bornes',
        'control_filtro_aa_limpieza' => 'Control de filtro AA: limpieza',
        'control_etiqueta_check_torque' => 'Control de etiqueta de check de torque',
        'control_etiqueta_service' => 'Control de etiqueta de service',
    ];

    protected function casts(): array
    {
        return [
            'partes' => 'array',
            'control_unidad' => 'array',
            'kilometraje_actual' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function conductorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conductor_user_id');
    }

    public function servicioAsignado(): BelongsTo
    {
        return $this->belongsTo(ServicioAsignado::class);
    }

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class);
    }
}
