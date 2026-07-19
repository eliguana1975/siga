<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['deposito_id', 'provincia_id', 'ciudad_id', 'nombre', 'direccion', 'telefono', 'estado'])]
class Base extends Model
{
    use HasFactory;

    /**
     * Get the deposito associated with this base
     */
    public function deposito()
    {
        return $this->belongsTo(Deposito::class);
    }

    /**
     * Get the provincia associated with this base
     */
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    /**
     * Get the ciudad associated with this base
     */
    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class);
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class);
    }
}
