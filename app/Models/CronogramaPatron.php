<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'dias_trabajo', 'dias_descanso', 'estado', 'observaciones'])]
class CronogramaPatron extends Model
{
    use HasFactory;

    protected $table = 'cronograma_patrones';

    public function asignaciones(): HasMany
    {
        return $this->hasMany(CronogramaAsignacion::class, 'cronograma_patron_id');
    }

    public function cicloDias(): int
    {
        return (int) $this->dias_trabajo + (int) $this->dias_descanso;
    }
}
