<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Deposito extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'direccion', 
        'telefono', 
        'estado'
        ];

        // relaciones

        public function comprasTmp()
        {
            return $this->hasMany(CompraTmp::class, 'deposito_id');
        }

        public function inventarios(): HasMany
        {
            return $this->hasMany(Inventario::class, 'deposito_id');
        }

}
