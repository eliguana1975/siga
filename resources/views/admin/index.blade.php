@extends('layouts.admin')

@push('styles')
    <style>
        .dashboard-panel-scroll {
            max-height: 210px;
            overflow-y: auto;
            padding-right: .35rem;
        }

        .dashboard-panel-scroll::-webkit-scrollbar {
            width: 7px;
        }

        .dashboard-panel-scroll::-webkit-scrollbar-thumb {
            background: rgba(151, 164, 255, .45);
            border-radius: 999px;
        }

        .dashboard-panel-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .05);
            border-radius: 999px;
        }

        .dashboard-stat-chart {
            min-height: 250px;
        }

        .dashboard-chart-empty {
            min-height: 250px;
        }
    </style>
@endpush

@section('content')
    @php
        $activeDashboardKey = $activeDashboard?->key ?? null;
    @endphp

    <div class="page-heading">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h3>{{ $activeDashboard?->name ?? 'Dashboard' }}</h3>
                <p class="text-subtitle text-muted mb-0">
                    {{ $activeDashboard?->description ?? 'Resumen general del sistema.' }}
                </p>
            </div>
            @if (($availableDashboards ?? collect())->count() > 1)
                <div class="d-flex flex-wrap gap-2 align-items-start">
                    @foreach ($availableDashboards as $dashboard)
                        <a href="{{ route('admin.index', ['dashboard' => $dashboard->key]) }}"
                            class="btn btn-sm {{ $activeDashboardKey === $dashboard->key ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="{{ $dashboard->icon }}"></i> {{ $dashboard->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="page-content">
        @if (! empty($quickActions))
            <section class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Accesos rapidos</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach ($quickActions as $action)
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <a href="{{ $action['url'] }}" class="btn btn-{{ $action['variant'] }} w-100 h-100 d-flex align-items-center gap-2 text-start">
                                            <i class="{{ $action['icon'] }} fs-4"></i>
                                            <span>
                                                <span class="d-block fw-bold">{{ $action['label'] }}</span>
                                                <small class="d-block text-white-50">{{ $action['description'] }}</small>
                                            </span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if (! empty($priorityAlerts))
            <section class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Alertas prioritarias</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach ($priorityAlerts as $alert)
                                    @php
                                        $class = match ($alert['priority']) {
                                            'critica' => 'danger',
                                            'alta' => 'warning',
                                            default => 'info',
                                        };
                                    @endphp
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <a href="{{ $alert['url'] }}" class="alert alert-{{ $class }} d-block mb-0 text-decoration-none">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <strong>{{ $alert['title'] }}</strong>
                                                    <div>{{ $alert['detail'] }}</div>
                                                </div>
                                                <span class="badge bg-light-{{ $class }}">{{ ucfirst($alert['priority']) }}</span>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if (! $activeDashboard)
            <div class="alert alert-warning">
                No tienes dashboards asignados. Solicita al superusuario que habilite un dashboard para tu rol.
            </div>
        @elseif ($activeDashboardKey === 'choferes')
            <section class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-person-vcard fs-1 text-muted"></i>
                            <h4 class="mt-3">Dashboard de choferes</h4>
                            <p class="text-muted mb-0">Sin indicadores configurados por ahora.</p>
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($activeDashboardKey === 'compras-inventario')
            <section class="row">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Stock critico</h6><h4 class="font-extrabold mb-0">{{ $operationalAlerts['counts']['stock_critico'] ?? 0 }}</h4></div></div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Solicitudes demoradas</h6><h4 class="font-extrabold mb-0">{{ $operationalAlerts['counts']['solicitudes_demoradas'] ?? 0 }}</h4></div></div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Reparaciones vencidas</h6><h4 class="font-extrabold mb-0">{{ $operationalAlerts['counts']['reparaciones_vencidas'] ?? 0 }}</h4></div></div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Alertas totales</h6><h4 class="font-extrabold mb-0">{{ $operationalAlerts['counts']['total'] ?? 0 }}</h4></div></div>
                </div>
            </section>

            <section class="row">
                @php
                    $purchaseChartCards = [
                        [
                            'id' => 'purchase-alerts-chart',
                            'title' => 'Alertas por tipo',
                            'subtitle' => 'Distribucion de alertas activas.',
                            'data' => $purchaseInventoryCharts['alertas'] ?? ['labels' => [], 'series' => []],
                            'type' => 'donut',
                        ],
                        [
                            'id' => 'critical-stock-deposit-chart',
                            'title' => 'Stock critico por deposito',
                            'subtitle' => 'Articulos debajo del minimo por deposito.',
                            'data' => $purchaseInventoryCharts['stock_por_deposito'] ?? ['labels' => [], 'series' => []],
                            'type' => 'bar',
                        ],
                        [
                            'id' => 'requests-status-chart',
                            'title' => 'Solicitudes por estado',
                            'subtitle' => 'Estado actual de solicitudes de repuestos.',
                            'data' => $purchaseInventoryCharts['solicitudes_por_estado'] ?? ['labels' => [], 'series' => []],
                            'type' => 'bar',
                        ],
                        [
                            'id' => 'repairs-status-chart',
                            'title' => 'Reparaciones por estado',
                            'subtitle' => 'Seguimiento de reparaciones de articulos.',
                            'data' => $purchaseInventoryCharts['reparaciones_por_estado'] ?? ['labels' => [], 'series' => []],
                            'type' => 'donut',
                        ],
                    ];
                @endphp

                @foreach ($purchaseChartCards as $chartCard)
                    <div class="col-12 col-xl-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title mb-0">{{ $chartCard['title'] }}</h4>
                                <p class="text-muted mb-0">{{ $chartCard['subtitle'] }}</p>
                            </div>
                            <div class="card-body">
                                @if (array_sum($chartCard['data']['series']) > 0)
                                    <div id="{{ $chartCard['id'] }}" class="dashboard-stat-chart"></div>
                                @else
                                    <div class="dashboard-chart-empty d-flex align-items-center justify-content-center text-muted border rounded">
                                        Sin datos para graficar.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="row">
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Stock critico</h4></div>
                        <div class="card-body">
                            <div class="dashboard-panel-scroll">
                                @forelse (($operationalAlerts['stock_critico'] ?? []) as $alerta)
                                    <div class="border-bottom py-2">
                                        <div class="fw-semibold">{{ $alerta['articulo'] }}</div>
                                        <small class="text-muted">{{ $alerta['deposito'] }} - Stock {{ $alerta['cantidad'] }} / min {{ $alerta['stock_minimo'] }}</small>
                                    </div>
                                @empty
                                    <div class="text-muted">Sin stock critico.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Solicitudes demoradas</h4></div>
                        <div class="card-body">
                            <div class="dashboard-panel-scroll">
                                @forelse (($operationalAlerts['solicitudes_demoradas'] ?? []) as $alerta)
                                    <div class="border-bottom py-2">
                                        <div class="fw-semibold">#{{ $alerta['id'] }} - {{ $alerta['descripcion'] }}</div>
                                        <small class="text-muted">{{ $alerta['estado'] }} - {{ $alerta['dias_abierta'] ?? '-' }} dia(s) abierta</small>
                                    </div>
                                @empty
                                    <div class="text-muted">Sin solicitudes demoradas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Reparaciones vencidas</h4></div>
                        <div class="card-body">
                            <div class="dashboard-panel-scroll">
                                @forelse (($operationalAlerts['reparaciones_vencidas'] ?? []) as $alerta)
                                    <div class="border-bottom py-2">
                                        <div class="fw-semibold">{{ $alerta['numero_orden'] }}</div>
                                        <small class="text-muted">{{ $alerta['proveedor'] ?: 'Sin proveedor' }} - {{ $alerta['dias_vencida'] ?? '-' }} dia(s) vencida</small>
                                    </div>
                                @empty
                                    <div class="text-muted">Sin reparaciones vencidas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($activeDashboardKey === 'taller')
            <section class="row">
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Pendientes</h6><h4 class="font-extrabold mb-0">{{ $workOrderStats['pendiente'] }}</h4></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">En proceso</h6><h4 class="font-extrabold mb-0">{{ $workOrderStats['en_proceso'] }}</h4></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Completadas</h6><h4 class="font-extrabold mb-0">{{ $workOrderStats['completada'] }}</h4></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Vehiculos parados</h6><h4 class="font-extrabold mb-0">{{ $vehicleStoppedStats['total'] ?? 0 }}</h4></div></div></div>
            </section>

            <section class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0">Internos para realizar servicio</h4>
                                <p class="text-muted mb-0">Servicios vencidos por kilometraje u horas.</p>
                            </div>
                            <a href="{{ route('admin.servicios-kilometraje.index') }}" class="btn btn-primary">
                                <i class="bi bi-speedometer2"></i> Ver control completo
                            </a>
                        </div>
                        <div class="card-body">
                            @include('admin.partials.dashboard-servicios')
                        </div>
                    </div>
                </div>
            </section>
        @else
            @include('admin.partials.dashboard-general')
        @endif
    </div>

    @if ($activeDashboardKey === 'compras-inventario')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof ApexCharts === 'undefined') {
                        return;
                    }

                    const charts = [
                        {
                            id: 'purchase-alerts-chart',
                            type: 'donut',
                            labels: @json($purchaseInventoryCharts['alertas']['labels'] ?? []),
                            series: @json($purchaseInventoryCharts['alertas']['series'] ?? []),
                        },
                        {
                            id: 'critical-stock-deposit-chart',
                            type: 'bar',
                            labels: @json($purchaseInventoryCharts['stock_por_deposito']['labels'] ?? []),
                            series: @json($purchaseInventoryCharts['stock_por_deposito']['series'] ?? []),
                        },
                        {
                            id: 'requests-status-chart',
                            type: 'bar',
                            labels: @json($purchaseInventoryCharts['solicitudes_por_estado']['labels'] ?? []),
                            series: @json($purchaseInventoryCharts['solicitudes_por_estado']['series'] ?? []),
                        },
                        {
                            id: 'repairs-status-chart',
                            type: 'donut',
                            labels: @json($purchaseInventoryCharts['reparaciones_por_estado']['labels'] ?? []),
                            series: @json($purchaseInventoryCharts['reparaciones_por_estado']['series'] ?? []),
                        },
                    ];

                    const colors = ['#435ebe', '#ffcf3f', '#ff5b5c', '#00cfe8', '#39da8a', '#9694ff', '#fdac41', '#a3e635'];

                    charts.forEach(function (chart) {
                        const element = document.getElementById(chart.id);

                        if (!element || !chart.series.some(function (value) { return Number(value) > 0; })) {
                            return;
                        }

                        const common = {
                            chart: {
                                type: chart.type,
                                height: 250,
                                toolbar: { show: false },
                                foreColor: '#c7d2fe',
                            },
                            colors: colors,
                            tooltip: {
                                y: {
                                    formatter: function (value) {
                                        return Number(value).toLocaleString('es-AR');
                                    },
                                },
                            },
                        };

                        const options = chart.type === 'bar'
                            ? {
                                ...common,
                                series: [{ name: 'Total', data: chart.series.map(Number) }],
                                xaxis: { categories: chart.labels },
                                plotOptions: {
                                    bar: {
                                        borderRadius: 4,
                                        columnWidth: '48%',
                                    },
                                },
                                dataLabels: { enabled: false },
                            }
                            : {
                                ...common,
                                labels: chart.labels,
                                series: chart.series.map(Number),
                                legend: {
                                    position: 'bottom',
                                    fontSize: '12px',
                                },
                                dataLabels: { enabled: false },
                                stroke: {
                                    width: 2,
                                    colors: ['#1f1d2f'],
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '68%',
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    label: 'Total',
                                                },
                                            },
                                        },
                                    },
                                },
                            };

                        new ApexCharts(element, options).render();
                    });
                });
            </script>
        @endpush
    @endif
@endsection
