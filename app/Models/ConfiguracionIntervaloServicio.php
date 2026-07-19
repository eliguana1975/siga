<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionIntervaloServicio extends Model
{
    protected $table = 'configuracion_intervalos_servicio';

    protected $fillable = [
        'sistema',
        'nombre',
        'kilometros_intervalo',
        'unidad_intervalo',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'kilometros_intervalo' => 'integer',
    ];

    public const SISTEMAS = [
        'motor' => 'Motor',
        'caja_automatica' => 'Caja automatica',
        'diferencial' => 'Diferencial',
        'caja_transferencia_4x4' => 'Caja de transferencia 4x4',
    ];

    public const UNIDADES = [
        'km' => 'Kilometros',
        'horas' => 'Horas',
    ];

    public function sistemaLabel(): string
    {
        return self::SISTEMAS[$this->sistema] ?? (string) $this->sistema;
    }

    public function unidadCorta(): string
    {
        return ($this->unidad_intervalo ?? 'km') === 'horas' ? 'hs' : 'km';
    }

    public function etiqueta(): string
    {
        $intervalo = number_format((int) $this->kilometros_intervalo, 0, ',', '.');

        return trim($this->sistemaLabel() . ' - ' . $this->nombre . ' | cada ' . $intervalo . ' ' . $this->unidadCorta() . ' | ID ' . $this->id);
    }
}
