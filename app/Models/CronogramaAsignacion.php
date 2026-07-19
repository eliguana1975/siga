<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['empleado_id', 'cronograma_patron_id', 'fecha_inicio', 'fecha_fin', 'estado', 'observaciones'])]
class CronogramaAsignacion extends Model
{
    use HasFactory;

    protected $table = 'cronograma_asignaciones';

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function patron(): BelongsTo
    {
        return $this->belongsTo(CronogramaPatron::class, 'cronograma_patron_id');
    }

    public function aplicaEnFecha(Carbon $fecha): bool
    {
        if (! $this->fecha_inicio) {
            return false;
        }

        $fechaInicio = Carbon::parse($this->fecha_inicio)->startOfDay();
        $fechaFin = $this->fecha_fin ? Carbon::parse($this->fecha_fin)->endOfDay() : null;

        if ($fecha->lt($fechaInicio)) {
            return false;
        }

        if ($fechaFin && $fecha->gt($fechaFin)) {
            return false;
        }

        return true;
    }
}
