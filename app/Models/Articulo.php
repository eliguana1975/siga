<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['categoria_id', 'unidad_medida_id', 'nombre', 'codigo_producto', 'foto_articulo_1', 'foto_articulo_2', 'foto_articulo_3', 'stock_minimo', 'stock_maximo', 'stock_pedido', 'reposicion_modo', 'es_herramienta', 'es_ropa_epp', 'pasillo', 'estanteria', 'casillero', 'estado_item', 'observaciones'])]
class Articulo extends Model
{
    use HasFactory;

    protected $casts = [
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'stock_pedido' => 'integer',
        'es_herramienta' => 'boolean',
        'es_ropa_epp' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Articulo $articulo) {
            if ($articulo->exists && blank($articulo->codigo_producto)) {
                $articulo->codigo_producto = self::barcodeCodeForId((int) $articulo->id);
            }
        });

        static::created(function (Articulo $articulo) {
            if (blank($articulo->codigo_producto)) {
                $articulo->forceFill([
                    'codigo_producto' => self::barcodeCodeForId((int) $articulo->id),
                ])->saveQuietly();
            }
        });
    }

    public static function barcodeCodeForId(int $id): string
    {
        return 'ART' . str_pad((string) $id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get the categoria associated with this articulo
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Get the unidad_medida associated with this articulo
     */
    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function ordenesTrabajoUsado(): HasMany
    {
        return $this->hasMany(OrdenTrabajoArticulo::class);
    }

    public function comprasTmp()
    {
        return $this->hasMany(CompraTmp::class, 'articulo_id');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'articulo_id');
    }

    public function cubiertas(): HasMany
    {
        return $this->hasMany(Cubierta::class, 'articulo_id');
    }

    public function entregasHerramientas(): HasMany
    {
        return $this->hasMany(EntregaHerramientaDetalle::class, 'articulo_id');
    }

    public function entregasRopaEpp(): HasMany
    {
        return $this->hasMany(EntregaRopaEppDetalle::class, 'articulo_id');
    }

}
