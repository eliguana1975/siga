<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['empleado_id', 'fecha', 'tipo', 'descripcion'])]
class CronogramaNovedad extends Model
{
    use HasFactory;

    public const TIPOS = [
        'trabaja' => 'Trabaja',
        'descanso' => 'Descanso',
        'franco_compensatorio' => 'Franco compensatorio',
        'licencia' => 'Licencia',
        'feriado' => 'Feriado',
        'otro' => 'Otro',
    ];

    protected $table = 'cronograma_novedades';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
