<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudRepuesto extends Model
{
    protected $table = 'solicitudes_repuestos';

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'en_revision' => 'En revision',
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
        'catalogado' => 'Catalogado',
        'pedido_generado' => 'Pedido generado',
        'comprado' => 'Comprado',
        'ingresado' => 'Ingresado',
        'entregado_taller' => 'Entregado a taller',
        'colocado' => 'Colocado',
        'cerrado' => 'Cerrado',
    ];

    public const ESTADO_BADGES = [
        'pendiente' => 'bg-light-warning',
        'en_revision' => 'bg-light-info',
        'aprobado' => 'bg-light-success',
        'rechazado' => 'bg-light-danger',
        'catalogado' => 'bg-light-primary',
        'pedido_generado' => 'bg-light-primary',
        'comprado' => 'bg-light-info',
        'ingresado' => 'bg-light-success',
        'entregado_taller' => 'bg-light-success',
        'colocado' => 'bg-light-success',
        'cerrado' => 'bg-light-secondary',
    ];

    public const PRIORIDADES = [
        'normal' => 'Normal',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public const PRIORIDAD_BADGES = [
        'normal' => 'bg-light-secondary',
        'alta' => 'bg-light-warning',
        'urgente' => 'bg-light-danger',
    ];

    protected $fillable = [
        'solicitante_user_id',
        'procesado_por_user_id',
        'flota_id',
        'orden_trabajo_id',
        'articulo_id',
        'pedido_articulo_id',
        'deposito_id',
        'fecha_solicitud',
        'estado',
        'prioridad',
        'cantidad',
        'descripcion_repuesto',
        'codigo_repuesto',
        'nro_chasis',
        'proveedor_sugerido',
        'motivo',
        'observaciones_taller',
        'observaciones_compras',
        'foto_repuesto_path',
        'foto_contexto_path',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'cantidad' => 'integer',
    ];

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_user_id');
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por_user_id');
    }

    public function flota(): BelongsTo
    {
        return $this->belongsTo(Flota::class, 'flota_id');
    }

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function pedidoArticulo(): BelongsTo
    {
        return $this->belongsTo(PedidoArticulo::class, 'pedido_articulo_id');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_id');
    }

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst((string) $this->estado);
    }

    public function estadoBadge(): string
    {
        return self::ESTADO_BADGES[$this->estado] ?? 'bg-light-secondary';
    }

    public function prioridadLabel(): string
    {
        return self::PRIORIDADES[$this->prioridad] ?? ucfirst((string) $this->prioridad);
    }

    public function prioridadBadge(): string
    {
        return self::PRIORIDAD_BADGES[$this->prioridad] ?? 'bg-light-secondary';
    }
}
