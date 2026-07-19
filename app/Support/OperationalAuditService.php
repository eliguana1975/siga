<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class OperationalAuditService
{
    public function audit(int $diasOt = 30): array
    {
        $diasOt = max(1, $diasOt);
        $metrics = [
            'inventarios_stock_negativo' => DB::table('inventarios')->where('cantidad', '<', 0)->count(),
            'inventarios_duplicados_deposito_articulo' => DB::table('inventarios')
                ->select('deposito_id', 'articulo_id')
                ->groupBy('deposito_id', 'articulo_id')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
            'articulos_codigo_duplicado' => DB::table('articulos')
                ->whereNotNull('codigo_producto')
                ->where('codigo_producto', '<>', '')
                ->select('codigo_producto')
                ->groupBy('codigo_producto')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
            'herramientas_devolucion_exceso' => DB::table('entrega_herramienta_detalles')
                ->whereColumn('cantidad_devuelta', '>', 'cantidad_entregada')
                ->count(),
            'ropa_epp_devolucion_exceso' => DB::table('entrega_ropa_epp_detalles')
                ->whereColumn('cantidad_devuelta', '>', 'cantidad_entregada')
                ->count(),
            'reparaciones_devolucion_exceso' => DB::table('reparacion_articulo_detalles')
                ->whereColumn('cantidad_devuelta', '>', 'cantidad_enviada')
                ->count(),
            'reparaciones_cerradas_con_pendientes' => DB::table('reparaciones_articulos')
                ->join('reparacion_articulo_detalles', 'reparacion_articulo_detalles.reparacion_articulo_id', '=', 'reparaciones_articulos.id')
                ->whereIn('reparaciones_articulos.estado', ['completada', 'cancelada'])
                ->whereRaw('reparacion_articulo_detalles.cantidad_enviada > reparacion_articulo_detalles.cantidad_devuelta')
                ->distinct('reparaciones_articulos.id')
                ->count('reparaciones_articulos.id'),
            'ot_abiertas_antiguas' => DB::table('ordenes_trabajo')
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->where('fecha_orden', '<', now()->subDays($diasOt))
                ->count(),
            'ot_sin_articulos' => DB::table('ordenes_trabajo')
                ->leftJoin('orden_trabajo_articulos', 'orden_trabajo_articulos.orden_trabajo_id', '=', 'ordenes_trabajo.id')
                ->whereIn('ordenes_trabajo.estado', ['en_proceso', 'completada'])
                ->whereNull('orden_trabajo_articulos.id')
                ->count(),
            'solicitudes_pedido_sin_articulo' => DB::table('solicitudes_repuestos')
                ->whereNotNull('pedido_articulo_id')
                ->whereNull('articulo_id')
                ->count(),
            'pedidos_activos_detalle_duplicado' => DB::table('pedidos_articulo')
                ->join('pedido_detalle_articulo', 'pedido_detalle_articulo.pedidos_articulo_id', '=', 'pedidos_articulo.id')
                ->whereIn('pedidos_articulo.estado', ['pendiente', 'aprobado', 'en_compra'])
                ->select('pedidos_articulo.id', 'pedido_detalle_articulo.articulo_id')
                ->groupBy('pedidos_articulo.id', 'pedido_detalle_articulo.articulo_id')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
        ];

        $issues = collect([
            ['metric' => 'inventarios_stock_negativo', 'codigo' => 'INV_NEGATIVO', 'severidad' => 'critica', 'detalle' => 'Inventarios con stock negativo'],
            ['metric' => 'inventarios_duplicados_deposito_articulo', 'codigo' => 'INV_DUPLICADO', 'severidad' => 'alta', 'detalle' => 'Inventarios duplicados para el mismo deposito y articulo'],
            ['metric' => 'articulos_codigo_duplicado', 'codigo' => 'ART_COD_DUP', 'severidad' => 'alta', 'detalle' => 'Codigos de articulo duplicados'],
            ['metric' => 'herramientas_devolucion_exceso', 'codigo' => 'HERR_DEV_EXCESO', 'severidad' => 'critica', 'detalle' => 'Herramientas con devolucion mayor a entregado'],
            ['metric' => 'ropa_epp_devolucion_exceso', 'codigo' => 'EPP_DEV_EXCESO', 'severidad' => 'critica', 'detalle' => 'Ropa/EPP con devolucion mayor a entregado'],
            ['metric' => 'reparaciones_devolucion_exceso', 'codigo' => 'REP_DEV_EXCESO', 'severidad' => 'critica', 'detalle' => 'Reparaciones con devolucion mayor a enviado'],
            ['metric' => 'reparaciones_cerradas_con_pendientes', 'codigo' => 'REP_CERRADA_PEND', 'severidad' => 'alta', 'detalle' => 'Reparaciones cerradas/canceladas con cantidades pendientes'],
            ['metric' => 'ot_abiertas_antiguas', 'codigo' => 'OT_ABIERTA_ANTIGUA', 'severidad' => 'media', 'detalle' => 'OT abiertas con mas de ' . $diasOt . ' dias'],
            ['metric' => 'ot_sin_articulos', 'codigo' => 'OT_SIN_ARTICULOS', 'severidad' => 'media', 'detalle' => 'OT en proceso/completadas sin articulos asociados'],
            ['metric' => 'solicitudes_pedido_sin_articulo', 'codigo' => 'SOL_PED_SIN_ART', 'severidad' => 'alta', 'detalle' => 'Solicitudes con pedido generado pero sin articulo asociado'],
            ['metric' => 'pedidos_activos_detalle_duplicado', 'codigo' => 'PED_DET_DUP', 'severidad' => 'media', 'detalle' => 'Pedidos activos con el mismo articulo repetido'],
        ])
            ->filter(fn (array $issue) => ($metrics[$issue['metric']] ?? 0) > 0)
            ->map(fn (array $issue) => $issue + ['total' => $metrics[$issue['metric']]])
            ->values()
            ->all();

        return [
            'status' => empty($issues) ? 'ok' : 'issues',
            'checked_at' => now()->toIso8601String(),
            'dias_ot' => $diasOt,
            'metrics' => $metrics,
            'issues' => $issues,
        ];
    }
}
