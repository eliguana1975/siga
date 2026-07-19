<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['usuario_id', 'deposito_id', 'base_id', 'nombres', 'apellidos', 'tipo_empleado', 'turno_laboral', 'es_franquero', 'franquero_de_tipo_empleado', 'franquero_de_empleado_id', 'tipo_doc', 'numero_doc', 'telefono', 'direccion', 'fecha_nacimiento', 'categoria_carnet_conducir', 'vencimiento_carnet_conducir', 'vencimiento_linti', 'estado'])]
class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'vencimiento_carnet_conducir' => 'date',
            'vencimiento_linti' => 'date',
            'es_franquero' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class);
    }

    public function entregasHerramientas(): HasMany
    {
        return $this->hasMany(EntregaHerramienta::class);
    }

    public function cronogramaAsignaciones(): HasMany
    {
        return $this->hasMany(CronogramaAsignacion::class);
    }

    public function cronogramaNovedades(): HasMany
    {
        return $this->hasMany(CronogramaNovedad::class);
    }

    public function franqueroDeEmpleado(): BelongsTo
    {
        return $this->belongsTo(self::class, 'franquero_de_empleado_id');
    }

    public function franqueroReemplazos(): HasMany
    {
        return $this->hasMany(self::class, 'franquero_de_empleado_id');
    }
}
