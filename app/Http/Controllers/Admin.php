<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionIntervaloServicio;
use App\Models\ControlUnidad;
use App\Models\Dashboard;
use App\Models\Empleado;
use App\Models\Flota;
use App\Models\Inventario;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoArticulo;
use App\Models\ReparacionArticulo;
use App\Models\RegistroServicioKilometraje;
use App\Models\RegistroVerificacionTecnica;
use App\Models\ServicioAsignado;
use App\Models\SolicitudRepuesto;
use App\Support\OperationalAlertService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Admin extends Controller
{

    public function index(OperationalAlertService $alerts)
    {
        $user = Auth::user();
        $availableDashboards = Dashboard::availableFor($user);
        $requestedDashboardKey = request('dashboard');
        $defaultDashboardId = $user?->dashboard_id;

        $activeDashboard = ($requestedDashboardKey ? $availableDashboards->firstWhere('key', $requestedDashboardKey) : null)
            ?? ($defaultDashboardId ? $availableDashboards->firstWhere('id', $defaultDashboardId) : null)
            ?? $availableDashboards->firstWhere('key', 'general')
            ?? $availableDashboards->first();

        $fleetStatusCounts = Flota::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $fleetStats = [
            'total' => (int) $fleetStatusCounts->sum(),
            'activo' => (int) ($fleetStatusCounts['activo'] ?? 0),
            'baja' => (int) ($fleetStatusCounts['baja'] ?? 0),
            'mantenimiento' => (int) ($fleetStatusCounts['mantenimiento'] ?? 0),
        ];

        $workOrderStatusCounts = OrdenTrabajo::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $workOrderStats = [
            'total' => (int) $workOrderStatusCounts->sum(),
            'pendiente' => (int) ($workOrderStatusCounts['pendiente'] ?? 0),
            'en_proceso' => (int) ($workOrderStatusCounts['en_proceso'] ?? 0),
            'completada' => (int) ($workOrderStatusCounts['completada'] ?? 0),
            'cancelada' => (int) ($workOrderStatusCounts['cancelada'] ?? 0),
        ];

        $serviciosParaRealizar = $this->serviciosParaRealizar();
        $vencimientosVerificaciones = $this->vencimientosVerificacionesProximos();
        $vencimientosCarnetsConducir = $this->vencimientosCarnetsConducirProximos();
        $vencimientosMatafuegos = $this->vencimientosMatafuegosProximos();
        $vehicleStoppedStats = $this->vehicleStoppedStats();
        $serviceAssignedStats = $this->serviceAssignedStats();
        $vehicleTypeStats = $this->vehicleTypeStats();
        $operationalAlerts = $alerts->summary();
        $quickActions = $this->quickActionsForUser($user);
        $priorityAlerts = $this->priorityAlerts($operationalAlerts, $vencimientosVerificaciones, $vencimientosCarnetsConducir, $vencimientosMatafuegos);
        $purchaseInventoryCharts = $this->purchaseInventoryCharts($operationalAlerts);
        $dashboardChartPreferences = data_get(Auth::user()?->dashboard_preferences, 'chart_types', []);

        return view('admin.index', compact(
            'fleetStats',
            'workOrderStats',
            'serviciosParaRealizar',
            'vencimientosVerificaciones',
            'vencimientosCarnetsConducir',
            'vencimientosMatafuegos',
            'vehicleStoppedStats',
            'serviceAssignedStats',
            'vehicleTypeStats',
            'operationalAlerts',
            'quickActions',
            'priorityAlerts',
            'purchaseInventoryCharts',
            'dashboardChartPreferences',
            'availableDashboards',
            'activeDashboard'
        ));
    }

    public function updateDashboardPreferences(Request $request)
    {
        $validated = $request->validate([
            'chart_types' => ['required', 'array'],
            'chart_types.fleet-status-chart' => ['nullable', 'in:donut,pie,bar'],
            'chart_types.work-order-status-chart' => ['nullable', 'in:donut,pie,bar'],
            'chart_types.vehicle-stopped-chart' => ['nullable', 'in:donut,pie,bar'],
            'chart_types.service-assigned-chart' => ['nullable', 'in:donut,pie,bar'],
            'chart_types.vehicle-type-chart' => ['nullable', 'in:donut,pie,bar'],
        ]);

        $user = $request->user();
        $preferences = $user->dashboard_preferences ?? [];
        $chartTypes = array_filter(
            $validated['chart_types'],
            fn ($chartType) => in_array($chartType, ['donut', 'pie', 'bar'], true)
        );

        $preferences['chart_types'] = array_merge($preferences['chart_types'] ?? [], $chartTypes);
        $user->forceFill(['dashboard_preferences' => $preferences])->save();

        return response()->json([
            'ok' => true,
            'chart_types' => $preferences['chart_types'],
        ]);
    }

    private function vehicleStoppedStats(): array
    {
        $labels = [
            'repuestos' => 'Repuestos',
            'terceros' => 'Terceros',
            'taller' => 'Taller',
            'compras' => 'Compras',
            'autorizacion' => 'Autorizacion',
            'otro' => 'Otro',
        ];

        $counts = OrdenTrabajo::query()
            ->select('motivo_vehiculo_parado', DB::raw('COUNT(DISTINCT flota_id) as total'))
            ->where('vehiculo_parado', true)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->groupBy('motivo_vehiculo_parado')
            ->pluck('total', 'motivo_vehiculo_parado');

        $series = [];
        $chartLabels = [];

        foreach ($labels as $key => $label) {
            $total = (int) ($counts[$key] ?? 0);

            if ($total <= 0) {
                continue;
            }

            $chartLabels[] = $label;
            $series[] = $total;
        }

        return [
            'total' => array_sum($series),
            'labels' => $chartLabels,
            'series' => $series,
        ];
    }

    private function quickActionsForUser($user): array
    {
        if (! $user) {
            return [];
        }

        $roles = $user->roles->pluck('name')->map(fn ($role) => mb_strtoupper((string) $role, 'UTF-8'));
        $isChofer = $roles->contains('CHOFER');
        $isMecanico = $roles->contains('MECANICO');
        $isTaller = $roles->contains(fn ($role) => in_array($role, ['JEFE_TALLER', 'JEFE DE TALLER', 'MECANICO'], true));

        $actions = [];

        if ($user->can('controles-unidad.crear')) {
            $actions[] = [
                'label' => 'Nuevo checklist',
                'description' => 'Registrar control vehicular',
                'icon' => 'bi bi-card-checklist',
                'url' => route('admin.controles-unidad.create'),
                'variant' => 'primary',
            ];
        }

        if ($isTaller && $user->can('ordenes-trabajo.ver')) {
            $actions[] = [
                'label' => $user->can('ordenes-trabajo.crear') ? 'Nueva OT' : 'Ver OT',
                'description' => 'Gestionar trabajos de taller',
                'icon' => 'bi bi-wrench-adjustable',
                'url' => route('admin.ordenes-trabajo.index'),
                'variant' => 'success',
            ];
        }

        if (! $isChofer && $user->can('solicitudes-repuestos.crear')) {
            $actions[] = [
                'label' => 'Solicitar repuesto',
                'description' => 'Pedido rapido a compras',
                'icon' => 'bi bi-cart-plus',
                'url' => route('admin.solicitudes-repuestos.create'),
                'variant' => 'warning',
            ];
        }

        if (! $isChofer && $user->can('bi.ver')) {
            $actions[] = [
                'label' => 'Estadisticas de flota',
                'description' => 'Analizar costos y fallas',
                'icon' => 'bi bi-bar-chart-line',
                'url' => route('admin.bi.flota-estadisticas'),
                'variant' => 'info',
            ];
        }

        if ($user->can('inventarios.ver')) {
            $actions[] = [
                'label' => 'Stock critico',
                'description' => 'Revisar faltantes',
                'icon' => 'bi bi-box-seam',
                'url' => route('admin.inventarios.bajo-stock'),
                'variant' => 'danger',
            ];
        }

        return array_slice($actions, 0, $isMecanico ? 4 : 6);
    }

    private function priorityAlerts(array $operationalAlerts, $vencimientosVerificaciones, $vencimientosCarnetsConducir, $vencimientosMatafuegos): array
    {
        $alerts = [];

        $counts = $operationalAlerts['counts'] ?? [];
        if ((int) ($counts['stock_critico'] ?? 0) > 0) {
            $alerts[] = [
                'priority' => 'critica',
                'title' => 'Stock critico',
                'detail' => (int) $counts['stock_critico'] . ' articulo(s) debajo del minimo',
                'url' => route('admin.inventarios.bajo-stock'),
            ];
        }

        if ((int) ($counts['reparaciones_vencidas'] ?? 0) > 0) {
            $alerts[] = [
                'priority' => 'critica',
                'title' => 'Reparaciones vencidas',
                'detail' => (int) $counts['reparaciones_vencidas'] . ' reparacion(es) con compromiso vencido',
                'url' => route('admin.reparaciones-articulos.index'),
            ];
        }

        if (($vencimientosMatafuegos ?? collect())->isNotEmpty()) {
            $alerts[] = [
                'priority' => 'alta',
                'title' => 'Matafuegos por vencer',
                'detail' => $vencimientosMatafuegos->count() . ' matafuego(s) dentro de 30 dias',
                'url' => route('admin.index'),
            ];
        }

        if (($vencimientosVerificaciones ?? collect())->isNotEmpty()) {
            $alerts[] = [
                'priority' => 'alta',
                'title' => 'Vencimientos tecnicos',
                'detail' => $vencimientosVerificaciones->count() . ' verificacion(es) proximas',
                'url' => route('admin.verificaciones-tecnicas.index'),
            ];
        }

        if (($vencimientosCarnetsConducir ?? collect())->isNotEmpty()) {
            $alerts[] = [
                'priority' => 'media',
                'title' => 'Carnets por vencer',
                'detail' => $vencimientosCarnetsConducir->count() . ' carnet(s) proximos',
                'url' => route('admin.empleados.index'),
            ];
        }

        if ((int) ($counts['solicitudes_demoradas'] ?? 0) > 0) {
            $alerts[] = [
                'priority' => 'media',
                'title' => 'Solicitudes demoradas',
                'detail' => (int) $counts['solicitudes_demoradas'] . ' solicitud(es) abiertas hace mas de 3 dias',
                'url' => route('admin.solicitudes-repuestos.index'),
            ];
        }

        return collect($alerts)
            ->sortBy(fn (array $alert) => ['critica' => 1, 'alta' => 2, 'media' => 3][$alert['priority']] ?? 9)
            ->take(6)
            ->values()
            ->all();
    }

    private function purchaseInventoryCharts(array $operationalAlerts): array
    {
        $stockCriticoPorDeposito = Inventario::query()
            ->leftJoin('depositos', 'inventarios.deposito_id', '=', 'depositos.id')
            ->whereColumn('inventarios.cantidad', '<=', 'inventarios.stock_minimo')
            ->where('inventarios.stock_minimo', '>', 0)
            ->selectRaw("COALESCE(depositos.nombre, 'Sin deposito') as deposito, COUNT(*) as total")
            ->groupBy('deposito')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $solicitudesPorEstado = SolicitudRepuesto::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $reparacionesPorEstado = ReparacionArticulo::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'alertas' => [
                'labels' => ['Stock critico', 'Solicitudes demoradas', 'Reparaciones vencidas'],
                'series' => [
                    (int) data_get($operationalAlerts, 'counts.stock_critico', 0),
                    (int) data_get($operationalAlerts, 'counts.solicitudes_demoradas', 0),
                    (int) data_get($operationalAlerts, 'counts.reparaciones_vencidas', 0),
                ],
            ],
            'stock_por_deposito' => [
                'labels' => $stockCriticoPorDeposito->pluck('deposito')->map(fn ($deposito) => (string) $deposito)->all(),
                'series' => $stockCriticoPorDeposito->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'solicitudes_por_estado' => [
                'labels' => $solicitudesPorEstado->pluck('estado')->map(fn ($estado) => Str::headline((string) $estado))->all(),
                'series' => $solicitudesPorEstado->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'reparaciones_por_estado' => [
                'labels' => $reparacionesPorEstado->pluck('estado')->map(fn ($estado) => Str::headline((string) $estado))->all(),
                'series' => $reparacionesPorEstado->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
        ];
    }

    private function serviceAssignedStats(): array
    {
        $rows = ServicioAsignado::query()
            ->leftJoin('flota', 'servicios_asignados.id', '=', 'flota.servicio_asignado_actual_id')
            ->leftJoin('ordenes_trabajo', function ($join) {
                $join->on('flota.id', '=', 'ordenes_trabajo.flota_id')
                    ->where('ordenes_trabajo.vehiculo_parado', true)
                    ->whereIn('ordenes_trabajo.estado', ['pendiente', 'en_proceso']);
            })
            ->selectRaw('servicios_asignados.nombre as servicio, COUNT(DISTINCT ordenes_trabajo.flota_id) as total')
            ->groupBy('servicios_asignados.id', 'servicios_asignados.nombre')
            ->orderBy('servicios_asignados.nombre')
            ->get();

        return [
            'total' => (int) $rows->sum('total'),
            'labels' => $rows->pluck('servicio')->map(fn ($servicio) => (string) $servicio)->all(),
            'series' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
        ];
    }

    private function vehicleTypeStats(): array
    {
        $rows = Flota::query()
            ->leftJoin('tipo_vehiculo', 'flota.cod_tipo_vehiculo_id', '=', 'tipo_vehiculo.id')
            ->selectRaw("COALESCE(tipo_vehiculo.nombre, 'Sin tipo') as tipo, COUNT(*) as total")
            ->groupBy('tipo')
            ->orderBy('tipo')
            ->get();

        return [
            'total' => (int) $rows->sum('total'),
            'labels' => $rows->pluck('tipo')->map(fn ($tipo) => (string) $tipo)->all(),
            'series' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
        ];
    }

    private function serviciosParaRealizar()
    {
        $intervalos = ConfiguracionIntervaloServicio::query()
            ->select(['id', 'sistema', 'nombre', 'kilometros_intervalo', 'unidad_intervalo'])
            ->where('estado', 'activo')
            ->orderByRaw("CASE WHEN LOWER(sistema) = 'motor' OR LOWER(nombre) LIKE '%motor%' THEN 0 ELSE 1 END")
            ->orderBy('sistema')
            ->orderBy('nombre')
            ->get();

        if ($intervalos->isEmpty()) {
            return collect();
        }

        $ordenesKm = OrdenTrabajo::query()
            ->selectRaw('flota_id, MAX(kilometraje) as kilometraje')
            ->whereNotNull('flota_id')
            ->whereNotNull('kilometraje')
            ->groupBy('flota_id')
            ->pluck('kilometraje', 'flota_id');

        $controlesKm = ControlUnidad::query()
            ->selectRaw('flota_id, MAX(kilometraje_actual) as kilometraje')
            ->whereNotNull('flota_id')
            ->whereNotNull('kilometraje_actual')
            ->groupBy('flota_id')
            ->pluck('kilometraje', 'flota_id');

        $ultimosServicios = RegistroServicioKilometraje::query()
            ->with('intervalo')
            ->orderByDesc('fecha_servicio')
            ->orderByDesc('id')
            ->get()
            ->unique(fn (RegistroServicioKilometraje $registro) => $registro->flota_id . '-' . $registro->configuracion_intervalo_servicio_id)
            ->filter(fn (RegistroServicioKilometraje $registro) => $registro->flota_id && $registro->configuracion_intervalo_servicio_id)
            ->keyBy(fn (RegistroServicioKilometraje $registro) => $registro->flota_id . '-' . $registro->configuracion_intervalo_servicio_id);

        return Flota::query()
            ->select([
                'id',
                'nro_interno',
                'dominio',
                'marca_vehiculo_id',
                'cod_tipo_vehiculo_id',
                'tipo_caja_id',
                'modelo_caja_id',
                'tipo_medidor_servicio',
                'horometro_actual',
                'observaciones',
            ])
            ->with(['tipoVehiculo:id,nombre', 'marcaVehiculo:id,nombre', 'tipoCaja:id,nombre', 'modeloCaja:id,nombre'])
            ->where('estado', '!=', 'baja')
            ->orderBy('nro_interno')
            ->get()
            ->flatMap(function (Flota $flota) use ($intervalos, $ordenesKm, $controlesKm, $ultimosServicios) {
                $tipoMedidor = $this->tipoMedidorParaFlota($flota);
                $kilometrajeActual = max(
                    (int) ($ordenesKm[$flota->id] ?? 0),
                    (int) ($controlesKm[$flota->id] ?? 0)
                );
                $horometroActual = max((int) ($flota->horometro_actual ?? 0), 0);
                $lecturaActual = $tipoMedidor === 'horas' ? $horometroActual : $kilometrajeActual;

                if ($lecturaActual <= 0) {
                    return collect();
                }

                $intervalosCompatibles = $intervalos
                    ->filter(fn (ConfiguracionIntervaloServicio $intervalo) => $this->servicioCompatibleConCaja($flota, $intervalo))
                    ->filter(fn (ConfiguracionIntervaloServicio $intervalo) => $this->normalizeMedidor($intervalo->unidad_intervalo ?? 'km') === $tipoMedidor)
                    ->values();

                return $intervalosCompatibles->map(function (ConfiguracionIntervaloServicio $intervalo) use ($flota, $lecturaActual, $ultimosServicios) {
                    $unidad = $this->normalizeMedidor($intervalo->unidad_intervalo ?? 'km');
                    $valorIntervalo = max(1, (int) $intervalo->kilometros_intervalo);
                    $ultimoServicio = $ultimosServicios->get($flota->id . '-' . $intervalo->id);
                    $ultimoServicioValor = $unidad === 'horas'
                        ? (int) ($ultimoServicio?->horometro_servicio ?? 0)
                        : (int) ($ultimoServicio?->kilometraje_servicio ?? 0);
                    $valorBase = $ultimoServicio
                        ? min($ultimoServicioValor, $lecturaActual)
                        : 0;

                    if ($lecturaActual - $valorBase < $valorIntervalo) {
                        return null;
                    }

                    return [
                        'interno' => $flota->nro_interno,
                        'dominio' => $flota->dominio,
                        'vehiculo' => trim(($flota->marcaVehiculo?->nombre ?? '') . ' ' . ($flota->tipoVehiculo?->nombre ?? '')),
                        'lectura_actual' => $lecturaActual,
                        'unidad' => $unidad,
                        'sistema' => $intervalo->sistema,
                        'servicio' => $intervalo->nombre,
                        'intervalo' => $valorIntervalo,
                    ];
                })->filter();
            })
            ->values();
    }

    private function normalizeMedidor(?string $medidor): string
    {
        $medidor = trim((string) $medidor);

        return $medidor === 'horas' ? 'horas' : 'km';
    }

    private function tipoMedidorParaFlota(Flota $flota): string
    {
        $configured = $this->normalizeMedidor($flota->tipo_medidor_servicio ?? 'km');

        if ($configured === 'horas') {
            return 'horas';
        }

        $descriptor = $this->normalizarTexto(collect([
            $flota->tipoVehiculo?->nombre,
            $flota->modeloCaja?->nombre,
            $flota->observaciones,
        ])->filter()->implode(' '));

        $keywords = [
            'pala cargadora',
            'motoniveladora',
            'grupo electrogeno',
            'torre de luz',
            'compresor',
        ];

        return collect($keywords)->contains(fn (string $keyword) => str_contains($descriptor, $keyword))
            ? 'horas'
            : 'km';
    }

    private function servicioCompatibleConCaja(Flota $flota, ConfiguracionIntervaloServicio $intervalo): bool
    {
        $servicio = $this->normalizarTexto($intervalo->sistema . ' ' . $intervalo->nombre);

        if ($this->esServicioCajaTransferencia($servicio)) {
            return $this->vehiculoEs4x4($flota);
        }

        if (! str_contains($servicio, 'caja')) {
            return true;
        }

        $esManual = str_contains($servicio, 'manual');
        $esAutomatica = str_contains($servicio, 'automatica') || str_contains($servicio, 'automatico');

        if (! $esManual && ! $esAutomatica) {
            return true;
        }

        $tipoCaja = $this->normalizarTexto($flota->tipoCaja?->nombre ?? '');

        if ($tipoCaja === '') {
            return true;
        }

        if (str_contains($tipoCaja, 'manual')) {
            return $esManual;
        }

        if (str_contains($tipoCaja, 'automatica') || str_contains($tipoCaja, 'automatico')) {
            return $esAutomatica;
        }

        return true;
    }

    private function esServicioCajaTransferencia(string $servicio): bool
    {
        return str_contains($servicio, 'transferencia') || str_contains($servicio, '4x4') || str_contains($servicio, '4 x 4');
    }

    private function vehiculoEs4x4(Flota $flota): bool
    {
        $descripcionVehiculo = $this->normalizarTexto(collect([
            $flota->tipoVehiculo?->nombre,
            $flota->modeloCaja?->nombre,
            $flota->observaciones,
        ])->filter()->implode(' '));

        return str_contains($descripcionVehiculo, '4x4')
            || str_contains($descripcionVehiculo, '4 x 4')
            || str_contains($descripcionVehiculo, '4wd')
            || str_contains($descripcionVehiculo, 'awd')
            || str_contains($descripcionVehiculo, 'doble traccion');
    }

    private function normalizarTexto(string $texto): string
    {
        return mb_strtolower(Str::ascii($texto), 'UTF-8');
    }

    private function vencimientosVerificacionesProximos()
    {
        $desde = Carbon::today();
        $hasta = Carbon::today()->addDays(10);

        return RegistroVerificacionTecnica::query()
            ->with(['flota.marcaVehiculo', 'flota.tipoVehiculo', 'configuracion'])
            ->where('estado', 'vigente')
            ->whereBetween('fecha_vencimiento', [$desde->toDateString(), $hasta->toDateString()])
            ->whereHas('flota', fn ($query) => $query->where('estado', '!=', 'baja'))
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function (RegistroVerificacionTecnica $registro) use ($desde) {
                $diasRestantes = (int) $desde->diffInDays($registro->fecha_vencimiento, false);
                $flota = $registro->flota;

                return [
                    'interno' => $flota?->nro_interno ?? '-',
                    'dominio' => $flota?->dominio ?? '-',
                    'vehiculo' => trim(($flota?->marcaVehiculo?->nombre ?? '') . ' ' . ($flota?->tipoVehiculo?->nombre ?? '')),
                    'tipo' => $registro->configuracion?->nombre ?? 'Verificacion',
                    'fecha_vencimiento' => $registro->fecha_vencimiento,
                    'dias_restantes' => $diasRestantes,
                    'comprobante' => $registro->comprobante,
                ];
            });
    }

    private function vencimientosCarnetsConducirProximos()
    {
        $desde = Carbon::today();
        $hasta = Carbon::today()->addDays(10);

        return Empleado::query()
            ->where('estado', 'activo')
            ->whereNotNull('vencimiento_carnet_conducir')
            ->whereBetween('vencimiento_carnet_conducir', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('vencimiento_carnet_conducir')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get()
            ->map(function (Empleado $empleado) use ($desde) {
                $diasRestantes = (int) $desde->diffInDays($empleado->vencimiento_carnet_conducir, false);

                return [
                    'empleado' => trim($empleado->apellidos . ', ' . $empleado->nombres),
                    'tipo_empleado' => $empleado->tipo_empleado,
                    'categoria_carnet' => $empleado->categoria_carnet_conducir,
                    'fecha_vencimiento' => $empleado->vencimiento_carnet_conducir,
                    'dias_restantes' => $diasRestantes,
                    'telefono' => $empleado->telefono,
                ];
            });
    }

    private function vencimientosMatafuegosProximos()
    {
        $desde = Carbon::today();
        $hasta = Carbon::today()->addDays(30);

        return OrdenTrabajoArticulo::query()
            ->with(['articulo', 'ordenTrabajo.flota'])
            ->whereNotNull('matafuego_fecha_vencimiento')
            ->whereBetween('matafuego_fecha_vencimiento', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('matafuego_fecha_vencimiento')
            ->latest('id')
            ->get()
            ->map(function (OrdenTrabajoArticulo $detalle) use ($desde) {
                $orden = $detalle->ordenTrabajo;
                $flota = $orden?->flota;
                $fechaVencimiento = $detalle->matafuego_fecha_vencimiento;

                return [
                    'orden_id' => $orden?->id,
                    'interno' => $flota?->nro_interno ?? '-',
                    'dominio' => $flota?->dominio ?? '-',
                    'articulo' => $detalle->articulo?->nombre ?? 'Matafuego',
                    'numero' => $detalle->matafuego_numero,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'dias_restantes' => $fechaVencimiento ? (int) $desde->diffInDays($fechaVencimiento, false) : null,
                ];
            });
    }
}
