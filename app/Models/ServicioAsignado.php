<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre'])]
class ServicioAsignado extends Model
{
    use HasFactory;

    protected $table = 'servicios_asignados';

    public function controlesUnidad(): HasMany
    {
        return $this->hasMany(ControlUnidad::class);
    }
}
