<?php

namespace App\Support;

use App\Models\Inventario;
use App\Models\ReparacionArticulo;
use App\Models\SolicitudRepuesto;
use Carbon\Carbon;

class OperationalAlertService
{
    public function summary(int $limit = 8): array
    {
        $stockCritico = $this->stockCritico($limit);
        $solicitudesDemoradas = $this->solicitudesDemoradas($limit);
        $reparacionesVencidas = $this->reparacionesVencidas($limit);

        return [
            'generated_at' => now()->toIso8601String(),
            'counts' => [
                'stock_critico' => $stockCritico['total'],
                'solicitudes_demoradas' => $solicitudesDemoradas['total'],
                'reparaciones_vencidas' => $reparacionesVencidas['total'],
                'total' => $stockCritico['total'] + $solicitudesDemoradas['total'] + $reparacionesVencidas['total'],
            ],
            'stock_critico' => $stockCritico['items'],
            'solicitudes_demoradas' => $solicitudesDemoradas['items'],
            'reparaciones_vencidas' => $reparacionesVencidas['items'],
        ];
    }

    private function stockCritico(int $limit): array
    {
        $query = Inventario::query()
            ->with(['articulo:id,nombre,codigo_producto', 'deposito:id,nombre'])
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->where('stock_minimo', '>', 0);

        return [
            'total' => (clone $query)->count(),
            'items' => $query
                ->orderByRaw('(stock_minimo - cantidad) DESC')
                ->orderBy('id')
                ->limit($limit)
                ->get()
                ->map(fn (Inventario $inventario) => [
                    'inventario_id' => $inventario->id,
                    'articulo' => $inventario->articulo?->nombre ?? 'Articulo sin nombre',
                    'codigo' => $inventario->articulo?->codigo_producto,
                    'deposito' => $inventario->deposito?->nombre ?? 'Sin deposito',
                    'cantidad' => (int) $inventario->cantidad,
                    'stock_minimo' => (int) $inventario->stock_minimo,
                    'faltante' => max(0, (int) $inventario->stock_minimo - (int) $inventario->cantidad),
                ])
                ->values()
                ->all(),
        ];
    }

    private function solicitudesDemoradas(int $limit): array
    {
        $desde = Carbon::today()->subDays(3);
        $estadosAbiertos = ['pendiente', 'en_revision', 'aprobado', 'catalogado', 'pedido_generado', 'comprado', 'ingresado', 'entregado_taller', 'colocado'];
        $query = SolicitudRepuesto::query()
            ->with(['solicitante:id,name', 'flota:id,nro_interno,dominio'])
            ->whereIn('estado', $estadosAbiertos)
            ->where('fecha_solicitud', '<', $desde->startOfDay());

        return [
            'total' => (clone $query)->count(),
            'items' => $query
                ->oldest('fecha_solicitud')
                ->limit($limit)
                ->get()
                ->map(fn (SolicitudRepuesto $solicitud) => [
                    'id' => $solicitud->id,
                    'descripcion' => $solicitud->descripcion_repuesto,
                    'estado' => $solicitud->estado,
                    'prioridad' => $solicitud->prioridad,
                    'fecha_solicitud' => $solicitud->fecha_solicitud,
                    'dias_abierta' => $solicitud->fecha_solicitud ? (int) $solicitud->fecha_solicitud->diffInDays(now()) : null,
                    'solicitante' => $solicitud->solicitante?->name,
                    'interno' => $solicitud->flota?->nro_interno,
                    'dominio' => $solicitud->flota?->dominio,
                ])
                ->values()
                ->all(),
        ];
    }

    private function reparacionesVencidas(int $limit): array
    {
        $query = ReparacionArticulo::query()
            ->with(['proveedor:id,nombre'])
            ->whereDate('fecha_compromiso', '<', now()->toDateString())
            ->whereHas('detalles', fn ($detalles) => $detalles->whereRaw('cantidad_enviada > cantidad_devuelta'));

        return [
            'total' => (clone $query)->count(),
            'items' => $query
                ->oldest('fecha_compromiso')
                ->limit($limit)
                ->get()
                ->map(fn (ReparacionArticulo $reparacion) => [
                    'id' => $reparacion->id,
                    'numero_orden' => $reparacion->numero_orden,
                    'proveedor' => $reparacion->proveedor?->nombre,
                    'fecha_compromiso' => $reparacion->fecha_compromiso,
                    'dias_vencida' => $reparacion->fecha_compromiso ? (int) $reparacion->fecha_compromiso->diffInDays(now()) : null,
                    'cantidad_pendiente_total' => $reparacion->cantidadPendienteTotal(),
                    'estado' => $reparacion->estado,
                ])
                ->values()
                ->all(),
        ];
    }
}
