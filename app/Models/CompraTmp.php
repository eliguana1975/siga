<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraTmp extends Model
{
    protected $table = 'tmp_compra';

    protected $fillable = [
        'usuario_id',
        'deposito_id',
        'articulo_id',
        'proveedor_id',
        'precio_compra_unidad',
        'cantidad',
        'fecha_creacion',
        'estado',
    ];


    protected  $casts = [
        'fecha_creacion' => 'datetime',
        'precio_compra_unidad' => 'decimal:2',
    ];


    // relaciones

    public function articulos()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function deposito()
    {
        return $this->belongsTo(Deposito::class, 'deposito_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

}
