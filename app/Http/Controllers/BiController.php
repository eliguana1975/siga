<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\OrdenTrabajoArticulo;
use App\Models\ReparacionArticulo;
use App\Models\SolicitudRepuesto;
use App\Support\VehicleCostService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:bi.ver');
    }

    public function index()
    {
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

        return view('admin.bi.index', compact('datasets'));
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
