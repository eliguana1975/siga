<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ajuste extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pedidos_automaticos_activos' => 'boolean',
        'impuestos' => 'array',
    ];
}
