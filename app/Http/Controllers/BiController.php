<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\CambioCubierta;
use App\Models\ControlUnidad;
use App\Models\Flota;
use App\Models\OrdenTrabajoArticulo;
use App\Models\OrdenTrabajo;
use App\Models\ReparacionArticulo;
use App\Models\SolicitudRepuesto;
use App\Support\VehicleCostService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:bi.ver');
    }

    public function index()
    {
        $reportes = [
            [
                'nombre' => 'Estadisticas de flota',
                'descripcion' => 'Indicadores visuales por fecha y unidad: OT, checklists, costos, disponibilidad y fallas.',
                'icono' => 'bi bi-car-front',
                'url' => route('admin.bi.flota-estadisticas'),
            ],
        ];

        $datasets = [
            [
                'nombre' => 'Costeo por vehiculo',
                'descripcion' => 'Ranking de unidades por repuestos y cubiertas imputados a OT.',
                'url' => route('admin.bi.costeo-vehiculos'),
            ],
            [
                'nombre' => 'Stock critico',
                'descripcion' => 'Inventario debajo o igual al stock minimo.',
                'url' => route('admin.bi.stock-critico'),
            ],
            [
                'nombre' => 'Solicitudes de repuestos',
                'descripcion' => 'Solicitudes con estado, prioridad, vehiculo y fechas.',
                'url' => route('admin.bi.solicitudes-repuestos'),
            ],
            [
                'nombre' => 'Reparaciones vencidas',
                'descripcion' => 'Reparaciones con fecha compromiso vencida y devolucion pendiente.',
                'url' => route('admin.bi.reparaciones-vencidas'),
            ],
            [
                'nombre' => 'Reposicion sugerida',
                'descripcion' => 'Prediccion simple de reposicion por stock actual, minimos y consumo historico de OT.',
                'url' => route('admin.bi.reposicion-sugerida'),
            ],
        ];

        return view('admin.bi.index', compact('reportes', 'datasets'));
    }

    public function flotaEstadisticas(Request $request, VehicleCostService $service)
    {
        [$desde, $hasta] = $this->dateRange($request);
        $validated = $request->validate([
            'flota_id' => ['nullable', 'integer', 'exists:flota,id'],
        ]);
        $flotaId = isset($validated['flota_id']) ? (int) $validated['flota_id'] : null;

        $flotas = Flota::query()
            ->select('id', 'nro_interno', 'dominio')
            ->orderBy('nro_interno')
            ->get();

        $flotaQuery = Flota::query();
        if ($flotaId) {
            $flotaQuery->whereKey($flotaId);
        }

        $estadoFlota = (clone $flotaQuery)
            ->selectRaw("COALESCE(NULLIF(estado, ''), 'SIN ESTADO') as estado, COUNT(*) as total")
            ->groupByRaw("COALESCE(NULLIF(estado, ''), 'SIN ESTADO')")
            ->orderByDesc('total')
            ->pluck('total', 'estado')
            ->map(fn ($total) => (int) $total);

        $ordenesQuery = OrdenTrabajo::query()
            ->whereBetween('fecha_orden', [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId));

        $ordenesPorEstado = (clone $ordenesQuery)
            ->selectRaw("COALESCE(NULLIF(estado, ''), 'SIN ESTADO') as estado, COUNT(*) as total")
            ->groupByRaw("COALESCE(NULLIF(estado, ''), 'SIN ESTADO')")
            ->pluck('total', 'estado')
            ->map(fn ($total) => (int) $total);

        $ordenesPorTipo = (clone $ordenesQuery)
            ->selectRaw("COALESCE(NULLIF(tipo_trabajo, ''), 'SIN TIPO') as tipo, COUNT(*) as total")
            ->groupByRaw("COALESCE(NULLIF(tipo_trabajo, ''), 'SIN TIPO')")
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'tipo')
            ->map(fn ($total) => (int) $total);

        $ordenesPorPrioridad = (clone $ordenesQuery)
            ->selectRaw("COALESCE(NULLIF(prioridad, ''), 'SIN PRIORIDAD') as prioridad, COUNT(*) as total")
            ->groupByRaw("COALESCE(NULLIF(prioridad, ''), 'SIN PRIORIDAD')")
            ->pluck('total', 'prioridad')
            ->map(fn ($total) => (int) $total);

        $ordenesCerradas = (clone $ordenesQuery)
            ->whereNotNull('fecha_cierre')
            ->get(['fecha_orden', 'fecha_cierre']);
        $promedioCierreHoras = $ordenesCerradas->count() > 0
            ? round($ordenesCerradas->avg(fn (OrdenTrabajo $orden) => max(0, $orden->fecha_orden->diffInHours($orden->fecha_cierre))), 1)
            : null;

        $checklistsQuery = ControlUnidad::query()
            ->whereBetween('created_at', [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId));
        $checklists = (clone $checklistsQuery)->get(['partes', 'control_unidad']);
        $checklistsConObservaciones = $checklists->filter(fn (ControlUnidad $control) => $this->controlTieneIncidencias($control))->count();

        $solicitudesQuery = SolicitudRepuesto::query()
            ->whereBetween('fecha_solicitud', [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId));
        $solicitudesPorEstado = (clone $solicitudesQuery)
            ->selectRaw("COALESCE(NULLIF(estado, ''), 'SIN ESTADO') as estado, COUNT(*) as total")
            ->groupByRaw("COALESCE(NULLIF(estado, ''), 'SIN ESTADO')")
            ->pluck('total', 'estado')
            ->map(fn ($total) => (int) $total);

        $cambiosCubiertas = CambioCubierta::query()
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId))
            ->count();

        $costeo = $service->ranking($desde, $hasta, 100);
        if ($flotaId) {
            $costeo['ranking'] = collect($costeo['ranking'])->where('flota_id', $flotaId)->values()->all();
            $costeo['graficos'] = collect($costeo['graficos'])->where('flota_id', $flotaId)->values()->all();
            $costeo['totales']['vehiculos_con_costo'] = collect($costeo['ranking'])->where('total', '>', 0)->count();
            $costeo['totales']['repuestos'] = collect($costeo['ranking'])->sum('repuestos');
            $costeo['totales']['cubiertas'] = collect($costeo['ranking'])->sum('cubiertas');
            $costeo['totales']['total'] = collect($costeo['ranking'])->sum('total');
        }

        $ordenesPorVehiculo = $this->countByVehicle('ordenes_trabajo', 'fecha_orden', $desde, $hasta, $flotaId);
        $checklistsPorVehiculo = $this->countByVehicle('controles_unidad', 'created_at', $desde, $hasta, $flotaId);
        $solicitudesPorVehiculo = $this->countByVehicle('solicitudes_repuestos', 'fecha_solicitud', $desde, $hasta, $flotaId);
        $vehiculosParados = (clone $ordenesQuery)
            ->where('vehiculo_parado', true)
            ->whereNotNull('flota_id')
            ->distinct('flota_id')
            ->count('flota_id');

        $ranking = collect($costeo['ranking'])
            ->map(function (array $row) use ($ordenesPorVehiculo, $checklistsPorVehiculo, $solicitudesPorVehiculo) {
                $flotaId = (int) $row['flota_id'];
                return $row + [
                    'ordenes' => (int) ($ordenesPorVehiculo[$flotaId] ?? 0),
                    'checklists' => (int) ($checklistsPorVehiculo[$flotaId] ?? 0),
                    'solicitudes' => (int) ($solicitudesPorVehiculo[$flotaId] ?? 0),
                ];
            })
            ->sortByDesc(fn (array $row) => $row['total'] + ($row['ordenes'] * 1000) + ($row['solicitudes'] * 500))
            ->take(20)
            ->values();

        $diasPeriodo = max(1, (int) $desde->diffInDays($hasta) + 1);
        $periodoAnteriorHasta = $desde->copy()->subSecond();
        $periodoAnteriorDesde = $periodoAnteriorHasta->copy()->subDays($diasPeriodo - 1)->startOfDay();
        $costeoAnterior = $service->ranking($periodoAnteriorDesde, $periodoAnteriorHasta, 100);
        if ($flotaId) {
            $costeoAnterior['ranking'] = collect($costeoAnterior['ranking'])->where('flota_id', $flotaId)->values()->all();
            $costeoAnterior['totales']['total'] = collect($costeoAnterior['ranking'])->sum('total');
        }

        $comparacion = [
            'periodo_anterior_desde' => $periodoAnteriorDesde->toDateString(),
            'periodo_anterior_hasta' => $periodoAnteriorHasta->toDateString(),
            'ordenes' => $this->compareMetric(
                (clone $ordenesQuery)->count(),
                $this->countBetween('ordenes_trabajo', 'fecha_orden', $periodoAnteriorDesde, $periodoAnteriorHasta, $flotaId)
            ),
            'checklists' => $this->compareMetric(
                $checklists->count(),
                $this->countBetween('controles_unidad', 'created_at', $periodoAnteriorDesde, $periodoAnteriorHasta, $flotaId)
            ),
            'solicitudes' => $this->compareMetric(
                (clone $solicitudesQuery)->count(),
                $this->countBetween('solicitudes_repuestos', 'fecha_solicitud', $periodoAnteriorDesde, $periodoAnteriorHasta, $flotaId)
            ),
            'costo_total' => $this->compareMetric((float) $costeo['totales']['total'], (float) $costeoAnterior['totales']['total']),
        ];

        $mensual = [
            'labels' => $this->monthLabels($desde, $hasta),
            'ordenes' => $this->monthlyCounts('ordenes_trabajo', 'fecha_orden', $desde, $hasta, $flotaId),
            'checklists' => $this->monthlyCounts('controles_unidad', 'created_at', $desde, $hasta, $flotaId),
            'solicitudes' => $this->monthlyCounts('solicitudes_repuestos', 'fecha_solicitud', $desde, $hasta, $flotaId),
        ];

        $costosPorBase = $this->costsByBase($desde, $hasta, $flotaId);
        $rankingFallas = $this->failureRanking($desde, $hasta, $flotaId);

        $totales = [
            'flota_total' => (clone $flotaQuery)->count(),
            'ordenes' => (clone $ordenesQuery)->count(),
            'ordenes_abiertas' => (clone $ordenesQuery)->whereNotIn('estado', ['completada', 'cancelada'])->count(),
            'vehiculos_parados' => $vehiculosParados,
            'checklists' => $checklists->count(),
            'checklists_con_incidencias' => $checklistsConObservaciones,
            'solicitudes' => (clone $solicitudesQuery)->count(),
            'solicitudes_urgentes' => (clone $solicitudesQuery)->where('prioridad', 'urgente')->count(),
            'cambios_cubiertas' => $cambiosCubiertas,
            'costo_total' => (float) $costeo['totales']['total'],
            'promedio_cierre_horas' => $promedioCierreHoras,
        ];

        $charts = [
            'estadoFlota' => $this->series($estadoFlota),
            'ordenesPorEstado' => $this->series($ordenesPorEstado),
            'ordenesPorTipo' => $this->series($ordenesPorTipo),
            'ordenesPorPrioridad' => $this->series($ordenesPorPrioridad),
            'solicitudesPorEstado' => $this->series($solicitudesPorEstado),
            'costosRanking' => [
                'labels' => $ranking->take(10)->map(fn (array $row) => trim($row['interno'] . ' ' . $row['dominio']))->all(),
                'series' => $ranking->take(10)->map(fn (array $row) => round((float) $row['total'], 2))->all(),
            ],
            'disponibilidad' => [
                'labels' => ['DISPONIBLES', 'PARADOS'],
                'series' => [max(0, (int) $totales['flota_total'] - (int) $totales['vehiculos_parados']), (int) $totales['vehiculos_parados']],
            ],
            'mensual' => $mensual,
            'costosPorBase' => [
                'labels' => $costosPorBase->pluck('base')->all(),
                'series' => $costosPorBase->pluck('total')->map(fn ($total) => round((float) $total, 2))->all(),
            ],
            'rankingFallas' => [
                'labels' => $rankingFallas->pluck('motivo')->all(),
                'series' => $rankingFallas->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
        ];

        return view('admin.bi.flota-estadisticas', compact('desde', 'hasta', 'flotaId', 'flotas', 'totales', 'charts', 'ranking', 'comparacion', 'costosPorBase', 'rankingFallas'));
    }

    public function costeoVehiculos(Request $request, VehicleCostService $service): JsonResponse
    {
        [$desde, $hasta] = $this->dateRange($request);
        $data = $service->ranking($desde, $hasta, 100);

        return response()->json([
            'dataset' => 'costeo_vehiculos',
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'totales' => $data['totales'],
            'data' => $data['ranking'],
            'graficos' => $data['graficos'],
        ]);
    }

    public function stockCritico(): JsonResponse
    {
        $rows = Inventario::query()
            ->with(['articulo:id,nombre,codigo_producto,categoria_id,unidad_medida_id', 'articulo.categoria:id,nombre', 'articulo.unidadMedida:id,nombre', 'deposito:id,nombre'])
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->where('stock_minimo', '>', 0)
            ->orderByRaw('(stock_minimo - cantidad) DESC')
            ->limit(500)
            ->get()
            ->map(fn (Inventario $inventario) => [
                'inventario_id' => $inventario->id,
                'articulo_id' => $inventario->articulo_id,
                'articulo' => $inventario->articulo?->nombre,
                'codigo_producto' => $inventario->articulo?->codigo_producto,
                'categoria' => $inventario->articulo?->categoria?->nombre,
                'unidad_medida' => $inventario->articulo?->unidadMedida?->nombre,
                'deposito' => $inventario->deposito?->nombre,
                'cantidad' => (int) $inventario->cantidad,
                'stock_minimo' => (int) $inventario->stock_minimo,
                'stock_maximo' => (int) $inventario->stock_maximo,
                'faltante' => max(0, (int) $inventario->stock_minimo - (int) $inventario->cantidad),
                'valor_unitario' => (float) $inventario->precio_compra_unidad,
                'valor_stock' => (float) $inventario->precio_compra_unidad * (int) $inventario->cantidad,
            ])
            ->values();

        return response()->json([
            'dataset' => 'stock_critico',
            'generated_at' => now()->toIso8601String(),
            'total' => $rows->count(),
            'data' => $rows,
        ]);
    }

    public function solicitudesRepuestos(Request $request): JsonResponse
    {
        [$desde, $hasta] = $this->dateRange($request);

        $rows = SolicitudRepuesto::query()
            ->with(['solicitante:id,name', 'procesadoPor:id,name', 'flota:id,nro_interno,dominio', 'articulo:id,nombre,codigo_producto', 'pedidoArticulo:id,estado'])
            ->whereBetween('fecha_solicitud', [$desde, $hasta])
            ->latest('fecha_solicitud')
            ->limit(1000)
            ->get()
            ->map(fn (SolicitudRepuesto $solicitud) => [
                'id' => $solicitud->id,
                'fecha_solicitud' => $solicitud->fecha_solicitud?->toDateTimeString(),
                'estado' => $solicitud->estado,
                'estado_label' => $solicitud->estadoLabel(),
                'prioridad' => $solicitud->prioridad,
                'prioridad_label' => $solicitud->prioridadLabel(),
                'cantidad' => (int) $solicitud->cantidad,
                'descripcion_repuesto' => $solicitud->descripcion_repuesto,
                'codigo_repuesto' => $solicitud->codigo_repuesto,
                'solicitante' => $solicitud->solicitante?->name,
                'procesado_por' => $solicitud->procesadoPor?->name,
                'interno' => $solicitud->flota?->nro_interno,
                'dominio' => $solicitud->flota?->dominio,
                'articulo' => $solicitud->articulo?->nombre,
                'articulo_codigo' => $solicitud->articulo?->codigo_producto,
                'pedido_estado' => $solicitud->pedidoArticulo?->estado,
            ])
            ->values();

        return response()->json([
            'dataset' => 'solicitudes_repuestos',
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'total' => $rows->count(),
            'data' => $rows,
        ]);
    }

    public function reparacionesVencidas(): JsonResponse
    {
        $rows = ReparacionArticulo::query()
            ->with(['proveedor:id,nombre', 'detalles:id,reparacion_articulo_id,cantidad_enviada,cantidad_devuelta,costo_unitario,estado'])
            ->whereDate('fecha_compromiso', '<', now()->toDateString())
            ->whereHas('detalles', fn ($detalles) => $detalles->whereRaw('cantidad_enviada > cantidad_devuelta'))
            ->oldest('fecha_compromiso')
            ->limit(500)
            ->get()
            ->map(fn (ReparacionArticulo $reparacion) => [
                'id' => $reparacion->id,
                'numero_orden' => $reparacion->numero_orden,
                'proveedor' => $reparacion->proveedor?->nombre,
                'fecha_envio' => $reparacion->fecha_envio?->toDateString(),
                'fecha_compromiso' => $reparacion->fecha_compromiso?->toDateString(),
                'dias_vencida' => $reparacion->fecha_compromiso ? (int) $reparacion->fecha_compromiso->diffInDays(now()) : null,
                'estado' => $reparacion->estado,
                'cantidad_pendiente_total' => $reparacion->cantidadPendienteTotal(),
                'costo_estimado_pendiente' => (float) $reparacion->detalles->sum(fn ($detalle) => $detalle->cantidadPendiente() * (float) ($detalle->costo_unitario ?? 0)),
            ])
            ->values();

        return response()->json([
            'dataset' => 'reparaciones_vencidas',
            'generated_at' => now()->toIso8601String(),
            'total' => $rows->count(),
            'data' => $rows,
        ]);
    }

    public function reposicionSugerida(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dias_historial' => ['nullable', 'integer', 'min:30', 'max:365'],
            'dias_cobertura' => ['nullable', 'integer', 'min:7', 'max:180'],
        ]);
        $diasHistorial = (int) ($validated['dias_historial'] ?? 90);
        $diasCobertura = (int) ($validated['dias_cobertura'] ?? 30);
        $desde = now()->subDays($diasHistorial)->startOfDay();

        $consumos = OrdenTrabajoArticulo::query()
            ->selectRaw('articulo_id, SUM(cantidad) as consumo_total, COUNT(DISTINCT orden_trabajo_id) as ordenes_total')
            ->where('created_at', '>=', $desde)
            ->groupBy('articulo_id')
            ->get()
            ->keyBy('articulo_id');

        $rows = Inventario::query()
            ->with(['articulo:id,nombre,codigo_producto,categoria_id,unidad_medida_id', 'articulo.categoria:id,nombre', 'articulo.unidadMedida:id,nombre', 'deposito:id,nombre'])
            ->whereHas('articulo')
            ->orderBy('articulo_id')
            ->orderBy('deposito_id')
            ->limit(1000)
            ->get()
            ->map(function (Inventario $inventario) use ($consumos, $diasHistorial, $diasCobertura) {
                $consumo = $consumos->get($inventario->articulo_id);
                $consumoTotal = (int) ($consumo?->consumo_total ?? 0);
                $promedioDiario = $consumoTotal > 0 ? $consumoTotal / $diasHistorial : 0;
                $demandaCobertura = (int) ceil($promedioDiario * $diasCobertura);
                $objetivo = max((int) $inventario->stock_minimo, $demandaCobertura);

                if ((int) $inventario->stock_maximo > 0) {
                    $objetivo = min(max($objetivo, (int) $inventario->stock_minimo), (int) $inventario->stock_maximo);
                }

                $cantidadActual = (int) $inventario->cantidad;
                $cantidadSugerida = max(0, $objetivo - $cantidadActual);
                $diasCoberturaActual = $promedioDiario > 0 ? round($cantidadActual / $promedioDiario, 1) : null;

                return [
                    'inventario_id' => $inventario->id,
                    'articulo_id' => $inventario->articulo_id,
                    'articulo' => $inventario->articulo?->nombre,
                    'codigo_producto' => $inventario->articulo?->codigo_producto,
                    'categoria' => $inventario->articulo?->categoria?->nombre,
                    'unidad_medida' => $inventario->articulo?->unidadMedida?->nombre,
                    'deposito' => $inventario->deposito?->nombre,
                    'cantidad_actual' => $cantidadActual,
                    'stock_minimo' => (int) $inventario->stock_minimo,
                    'stock_maximo' => (int) $inventario->stock_maximo,
                    'consumo_total_periodo' => $consumoTotal,
                    'ordenes_con_consumo' => (int) ($consumo?->ordenes_total ?? 0),
                    'promedio_diario' => round($promedioDiario, 3),
                    'dias_cobertura_actual' => $diasCoberturaActual,
                    'objetivo_stock' => $objetivo,
                    'cantidad_sugerida' => $cantidadSugerida,
                    'prioridad' => $this->reposicionPrioridad($cantidadSugerida, $cantidadActual, (int) $inventario->stock_minimo, $diasCoberturaActual),
                    'valor_estimado_reposicion' => round($cantidadSugerida * (float) $inventario->precio_compra_unidad, 2),
                ];
            })
            ->filter(fn (array $row) => $row['cantidad_sugerida'] > 0 || $row['cantidad_actual'] <= $row['stock_minimo'])
            ->sortBy([
                ['prioridad', 'asc'],
                ['cantidad_sugerida', 'desc'],
            ])
            ->values();

        return response()->json([
            'dataset' => 'reposicion_sugerida',
            'generated_at' => now()->toIso8601String(),
            'dias_historial' => $diasHistorial,
            'dias_cobertura' => $diasCobertura,
            'total' => $rows->count(),
            'data' => $rows,
        ]);
    }

    private function reposicionPrioridad(int $sugerida, int $actual, int $minimo, ?float $diasCoberturaActual): string
    {
        if ($actual <= 0 || ($minimo > 0 && $actual < $minimo)) {
            return '1-critica';
        }

        if ($sugerida > 0 && $diasCoberturaActual !== null && $diasCoberturaActual <= 15) {
            return '2-alta';
        }

        if ($sugerida > 0) {
            return '3-media';
        }

        return '4-baja';
    }

    private function countByVehicle(string $table, string $dateColumn, Carbon $desde, Carbon $hasta, ?int $flotaId = null): array
    {
        return DB::table($table)
            ->selectRaw('flota_id, COUNT(*) as total')
            ->whereNotNull('flota_id')
            ->whereBetween($dateColumn, [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId))
            ->groupBy('flota_id')
            ->pluck('total', 'flota_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    private function countBetween(string $table, string $dateColumn, Carbon $desde, Carbon $hasta, ?int $flotaId = null): int
    {
        return DB::table($table)
            ->whereBetween($dateColumn, [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId))
            ->count();
    }

    private function compareMetric(float|int $actual, float|int $anterior): array
    {
        $variacion = $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : ($actual > 0 ? 100 : 0);

        return [
            'actual' => $actual,
            'anterior' => $anterior,
            'variacion' => round($variacion, 1),
        ];
    }

    private function monthLabels(Carbon $desde, Carbon $hasta): array
    {
        $labels = [];
        $cursor = $desde->copy()->startOfMonth();
        $end = $hasta->copy()->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $labels[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $labels;
    }

    private function monthlyCounts(string $table, string $dateColumn, Carbon $desde, Carbon $hasta, ?int $flotaId = null): array
    {
        $labels = $this->monthLabels($desde, $hasta);
        $rows = DB::table($table)
            ->selectRaw("DATE_FORMAT($dateColumn, '%Y-%m') as periodo, COUNT(*) as total")
            ->whereBetween($dateColumn, [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('flota_id', $flotaId))
            ->groupByRaw("DATE_FORMAT($dateColumn, '%Y-%m')")
            ->pluck('total', 'periodo')
            ->map(fn ($total) => (int) $total);

        return collect($labels)->map(fn (string $label) => (int) ($rows[$label] ?? 0))->all();
    }

    private function costsByBase(Carbon $desde, Carbon $hasta, ?int $flotaId = null)
    {
        $repuestos = DB::table('orden_trabajo_articulos')
            ->join('ordenes_trabajo', 'ordenes_trabajo.id', '=', 'orden_trabajo_articulos.orden_trabajo_id')
            ->leftJoin('detalle_cambio_cubiertas', 'detalle_cambio_cubiertas.orden_trabajo_articulo_id', '=', 'orden_trabajo_articulos.id')
            ->leftJoin('bases', 'bases.id', '=', 'ordenes_trabajo.base_id')
            ->whereNull('detalle_cambio_cubiertas.id')
            ->whereBetween('ordenes_trabajo.fecha_orden', [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('ordenes_trabajo.flota_id', $flotaId))
            ->groupByRaw("COALESCE(bases.nombre, 'SIN BASE')")
            ->selectRaw("COALESCE(bases.nombre, 'SIN BASE') as base, COALESCE(SUM(orden_trabajo_articulos.cantidad * orden_trabajo_articulos.valor_unitario), 0) as total")
            ->get();

        $cubiertas = DB::table('detalle_cambio_cubiertas')
            ->join('cambios_cubiertas', 'cambios_cubiertas.id', '=', 'detalle_cambio_cubiertas.cambio_cubierta_id')
            ->leftJoin('ordenes_trabajo', 'ordenes_trabajo.id', '=', 'cambios_cubiertas.orden_trabajo_id')
            ->leftJoin('bases', 'bases.id', '=', 'ordenes_trabajo.base_id')
            ->whereBetween('cambios_cubiertas.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($flotaId, fn ($query) => $query->where('cambios_cubiertas.flota_id', $flotaId))
            ->groupByRaw("COALESCE(bases.nombre, 'SIN BASE')")
            ->selectRaw("COALESCE(bases.nombre, 'SIN BASE') as base, COALESCE(SUM(detalle_cambio_cubiertas.valor_unitario), 0) as total")
            ->get();

        return $repuestos
            ->concat($cubiertas)
            ->groupBy('base')
            ->map(fn ($items, string $base) => [
                'base' => $base,
                'total' => (float) $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();
    }

    private function failureRanking(Carbon $desde, Carbon $hasta, ?int $flotaId = null)
    {
        return DB::table('orden_trabajo_motivo')
            ->join('ordenes_trabajo', 'ordenes_trabajo.id', '=', 'orden_trabajo_motivo.orden_trabajo_id')
            ->join('orden_trabajo_motivos', 'orden_trabajo_motivos.id', '=', 'orden_trabajo_motivo.orden_trabajo_motivo_id')
            ->whereBetween('ordenes_trabajo.fecha_orden', [$desde, $hasta])
            ->when($flotaId, fn ($query) => $query->where('ordenes_trabajo.flota_id', $flotaId))
            ->groupBy('orden_trabajo_motivos.id', 'orden_trabajo_motivos.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->selectRaw('orden_trabajo_motivos.nombre as motivo, COUNT(*) as total')
            ->get();
    }

    private function controlTieneIncidencias(ControlUnidad $control): bool
    {
        foreach ([$control->partes ?? [], $control->control_unidad ?? []] as $bloque) {
            foreach ($bloque as $items) {
                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $estado) {
                    if (in_array($estado, ['no_cumple', 'sin_hacer'], true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function series($items): array
    {
        $collection = collect($items);

        return [
            'labels' => $collection->keys()->map(fn ($label) => mb_strtoupper((string) $label, 'UTF-8'))->values()->all(),
            'series' => $collection->values()->map(fn ($total) => (int) $total)->all(),
        ];
    }

    private function dateRange(Request $request): array
    {
        $validated = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $desde = isset($validated['fecha_desde'])
            ? Carbon::parse($validated['fecha_desde'])->startOfDay()
            : now()->startOfMonth();
        $hasta = isset($validated['fecha_hasta'])
            ? Carbon::parse($validated['fecha_hasta'])->endOfDay()
            : now()->endOfDay();

        return [$desde, $hasta];
    }
}
