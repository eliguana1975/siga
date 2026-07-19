<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroVerificacionTecnica extends Model
{
    protected $table = 'registros_verificaciones_tecnicas';

    protected $fillable = [
        'flota_id',
        'configuracion_vencimiento_verificacion_id',
        'user_id',
        'fecha_emision',
        'fecha_vencimiento',
        'comprobante',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class);
    }

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionVencimientoVerificacion::class, 'configuracion_vencimiento_verificacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
