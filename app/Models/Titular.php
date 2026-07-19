<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'direccion', 'telefono', 'email', 'es_empresa'])]
class Titular extends Model
{
    use HasFactory;

    protected $table = 'titular';

    protected $casts = [
        'es_empresa' => 'boolean',
    ];
}
