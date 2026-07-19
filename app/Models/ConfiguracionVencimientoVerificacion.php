<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfiguracionVencimientoVerificacion extends Model
{
    protected $table = 'configuracion_vencimientos_verificacion';

    protected $fillable = [
        'tipo',
        'nombre',
        'dias_alerta',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'dias_alerta' => 'integer',
    ];

    public const TIPOS = [
        'TECNICA NACIONAL' => 'TECNICA NACIONAL',
        'TECNICA PROVINCIAL' => 'TECNICA PROVINCIAL',
        'CNRT' => 'CNRT',
    ];

    public function setTipoAttribute($value): void
    {
        $this->attributes['tipo'] = $this->uppercase($value);
    }

    public function setNombreAttribute($value): void
    {
        $this->attributes['nombre'] = $this->uppercase($value);
    }

    public function setObservacionesAttribute($value): void
    {
        $this->attributes['observaciones'] = $value === null ? null : $this->uppercase($value);
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroVerificacionTecnica::class, 'configuracion_vencimiento_verificacion_id');
    }

    private function uppercase($value): string
    {
        return mb_strtoupper(trim((string) $value), 'UTF-8');
    }
}
