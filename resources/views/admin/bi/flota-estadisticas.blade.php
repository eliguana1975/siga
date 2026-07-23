@extends('layouts.admin')

@push('styles')
    <style>
        .fleet-stat-card .card-body {
            min-height: 118px;
        }

        .fleet-stat-value {
            color: var(--bs-heading-color);
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1;
        }

        .fleet-stat-label {
            color: var(--bs-secondary-color);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .fleet-chart {
            min-height: 280px;
        }

        .fleet-chart-small {
            min-height: 240px;
        }

        .fleet-compare-value {
            font-size: .82rem;
            font-weight: 800;
        }

        .fleet-compare-up {
            color: #39da8a;
        }

        .fleet-compare-down {
            color: #ff5b5c;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3>Estadisticas de flota</h3>
                <p class="text-subtitle text-muted">Indicadores operativos filtrados por periodo y unidad.</p>
            </div>
            <a href="{{ route('admin.bi.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> BI
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bi.flota-estadisticas') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $desde->format('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $hasta->format('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Unidad</label>
                            <select name="flota_id" class="form-select">
                                <option value="">Toda la flota</option>
                                @foreach ($flotas as $flota)
                                    <option value="{{ $flota->id }}" @selected((int) $flotaId === (int) $flota->id)>
                                        {{ $flota->nro_interno ?: 'S/I' }} - {{ $flota->dominio ?: 'SIN DOMINIO' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.bi.flota-estadisticas') }}" class="btn btn-light-secondary">Mes actual</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-outline-secondary" onclick="exportTableToExcel('fleetStatsRankingTable', 'estadisticas_flota')">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="downloadCSVFromTable('fleetStatsRankingTable', 'estadisticas_flota')">
                    <i class="bi bi-file-earmark-arrow-down"></i> CSV
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="createPDF('fleetStatsRankingTable', 'estadisticas_flota')">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="printTable('fleetStatsRankingTable')">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Unidades</div>
                            <div class="fleet-stat-value mt-2">{{ number_format($totales['flota_total'], 0, ',', '.') }}</div>
                            <small class="text-muted">Segun filtro aplicado</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Ordenes de trabajo</div>
                            <div class="fleet-stat-value mt-2">{{ number_format($totales['ordenes'], 0, ',', '.') }}</div>
                            <small class="text-muted">{{ number_format($totales['ordenes_abiertas'], 0, ',', '.') }} abiertas</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Checklists</div>
                            <div class="fleet-stat-value mt-2">{{ number_format($totales['checklists'], 0, ',', '.') }}</div>
                            <small class="text-muted">{{ number_format($totales['checklists_con_incidencias'], 0, ',', '.') }} con incidencias</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Costo del periodo</div>
                            <div class="fleet-stat-value mt-2">$ {{ number_format($totales['costo_total'], 2, ',', '.') }}</div>
                            <small class="text-muted">Repuestos y cubiertas imputados</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Vehiculos parados</div>
                            <div class="fleet-stat-value mt-2">{{ number_format($totales['vehiculos_parados'], 0, ',', '.') }}</div>
                            <small class="text-muted">OT marcadas como vehiculo parado</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Solicitudes</div>
                            <div class="fleet-stat-value mt-2">{{ number_format($totales['solicitudes'], 0, ',', '.') }}</div>
                            <small class="text-muted">{{ number_format($totales['solicitudes_urgentes'], 0, ',', '.') }} urgentes</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Cambios de cubiertas</div>
                            <div class="fleet-stat-value mt-2">{{ number_format($totales['cambios_cubiertas'], 0, ',', '.') }}</div>
                            <small class="text-muted">Movimientos registrados</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card fleet-stat-card">
                        <div class="card-body">
                            <div class="fleet-stat-label">Promedio cierre OT</div>
                            <div class="fleet-stat-value mt-2">
                                {{ $totales['promedio_cierre_horas'] !== null ? number_format($totales['promedio_cierre_horas'], 1, ',', '.') . ' h' : '-' }}
                            </div>
                            <small class="text-muted">Desde apertura hasta cierre</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Comparacion contra periodo anterior</h4>
                    <small class="text-muted">
                        Periodo anterior: {{ \Carbon\Carbon::parse($comparacion['periodo_anterior_desde'])->format('d/m/Y') }}
                        al {{ \Carbon\Carbon::parse($comparacion['periodo_anterior_hasta'])->format('d/m/Y') }}
                    </small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ([
                            'ordenes' => 'Ordenes',
                            'checklists' => 'Checklists',
                            'solicitudes' => 'Solicitudes',
                            'costo_total' => 'Costo total',
                        ] as $key => $label)
                            @php
                                $item = $comparacion[$key];
                                $up = $item['variacion'] >= 0;
                            @endphp
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="fleet-stat-label">{{ $label }}</div>
                                    <div class="d-flex align-items-end justify-content-between gap-2 mt-2">
                                        <div class="fleet-stat-value">
                                            @if ($key === 'costo_total')
                                                $ {{ number_format($item['actual'], 2, ',', '.') }}
                                            @else
                                                {{ number_format($item['actual'], 0, ',', '.') }}
                                            @endif
                                        </div>
                                        <span class="fleet-compare-value {{ $up ? 'fleet-compare-up' : 'fleet-compare-down' }}">
                                            {{ $up ? '+' : '' }}{{ number_format($item['variacion'], 1, ',', '.') }}%
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Anterior:
                                        @if ($key === 'costo_total')
                                            $ {{ number_format($item['anterior'], 2, ',', '.') }}
                                        @else
                                            {{ number_format($item['anterior'], 0, ',', '.') }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Movimientos mensuales</h4></div>
                        <div class="card-body"><div id="chartMensual" class="fleet-chart-small"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Disponibilidad</h4></div>
                        <div class="card-body"><div id="chartDisponibilidad" class="fleet-chart-small"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Ordenes por estado</h4></div>
                        <div class="card-body"><div id="chartOrdenesEstado" class="fleet-chart-small"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Costo por unidad</h4></div>
                        <div class="card-body"><div id="chartCostosRanking" class="fleet-chart-small"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Estado de flota</h4></div>
                        <div class="card-body"><div id="chartEstadoFlota" class="fleet-chart"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">OT por tipo</h4></div>
                        <div class="card-body"><div id="chartOrdenesTipo" class="fleet-chart"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Solicitudes por estado</h4></div>
                        <div class="card-body"><div id="chartSolicitudesEstado" class="fleet-chart"></div></div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Costos por base</h4></div>
                        <div class="card-body"><div id="chartCostosBase" class="fleet-chart-small"></div></div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Ranking de fallas / motivos</h4></div>
                        <div class="card-body"><div id="chartRankingFallas" class="fleet-chart-small"></div></div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Detalle de costos por base</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Base</th>
                                            <th class="text-end">Costo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($costosPorBase as $base)
                                            <tr>
                                                <td>{{ $base['base'] }}</td>
                                                <td class="text-end fw-semibold">$ {{ number_format($base['total'], 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-4">Sin costos por base.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">Detalle de fallas / motivos</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Motivo</th>
                                            <th class="text-end">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rankingFallas as $falla)
                                            <tr>
                                                <td>{{ $falla->motivo }}</td>
                                                <td class="text-end fw-semibold">{{ number_format($falla->total, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-4">Sin motivos registrados.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ranking operativo por unidad</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="fleetStatsRankingTable" class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Interno</th>
                                    <th>Dominio</th>
                                    <th>Vehiculo</th>
                                    <th class="text-end">OT</th>
                                    <th class="text-end">Checklists</th>
                                    <th class="text-end">Solicitudes</th>
                                    <th class="text-end">Costo</th>
                                    <th class="text-end">Km periodo</th>
                                    <th class="text-end">Costo/km</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ranking as $row)
                                    <tr>
                                        <td class="fw-semibold">{{ $row['interno'] }}</td>
                                        <td>{{ $row['dominio'] }}</td>
                                        <td>{{ $row['vehiculo'] ?: '-' }}</td>
                                        <td class="text-end">{{ number_format($row['ordenes'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row['checklists'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row['solicitudes'], 0, ',', '.') }}</td>
                                        <td class="text-end fw-semibold">$ {{ number_format($row['total'], 2, ',', '.') }}</td>
                                        <td class="text-end">{{ $row['km_periodo'] > 0 ? number_format($row['km_periodo'], 0, ',', '.') : '-' }}</td>
                                        <td class="text-end">
                                            {{ $row['costo_por_km'] !== null ? '$ ' . number_format($row['costo_por_km'], 2, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No hay movimientos de flota en el periodo seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            const charts = @json($charts);
            const palette = ['#435ebe', '#00cfe8', '#39da8a', '#ffcf3f', '#ff5b5c', '#9694ff', '#fdac41'];
            const money = value => '$ ' + Number(value || 0).toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            function emptyChart(elementId) {
                const element = document.getElementById(elementId);
                if (element) {
                    element.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted border rounded">Sin datos para el periodo</div>';
                }
            }

            function donut(elementId, data) {
                if (!data.series.length) {
                    emptyChart(elementId);
                    return;
                }

                new ApexCharts(document.getElementById(elementId), {
                    chart: { type: 'donut', height: 260, toolbar: { show: false }, foreColor: '#c7d2fe' },
                    labels: data.labels,
                    series: data.series,
                    colors: palette,
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom' },
                    stroke: { width: 2, colors: ['#1f1d2f'] },
                }).render();
            }

            function bar(elementId, data, formatter) {
                if (!data.series.length) {
                    emptyChart(elementId);
                    return;
                }

                new ApexCharts(document.getElementById(elementId), {
                    chart: { type: 'bar', height: 260, toolbar: { show: false }, foreColor: '#c7d2fe' },
                    series: [{ name: 'Total', data: data.series }],
                    xaxis: { categories: data.labels },
                    colors: ['#435ebe'],
                    plotOptions: { bar: { borderRadius: 4, horizontal: false } },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: formatter || (value => Number(value || 0).toLocaleString('es-AR')) } },
                }).render();
            }

            function horizontalBar(elementId, data, formatter) {
                if (!data.series.length) {
                    emptyChart(elementId);
                    return;
                }

                new ApexCharts(document.getElementById(elementId), {
                    chart: { type: 'bar', height: 260, toolbar: { show: false }, foreColor: '#c7d2fe' },
                    series: [{ name: 'Total', data: data.series }],
                    xaxis: { categories: data.labels },
                    colors: ['#00cfe8'],
                    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: formatter || (value => Number(value || 0).toLocaleString('es-AR')) } },
                }).render();
            }

            function lineMonthly() {
                const data = charts.mensual;
                const total = data.ordenes.reduce((sum, value) => sum + value, 0)
                    + data.checklists.reduce((sum, value) => sum + value, 0)
                    + data.solicitudes.reduce((sum, value) => sum + value, 0);

                if (total <= 0) {
                    emptyChart('chartMensual');
                    return;
                }

                new ApexCharts(document.getElementById('chartMensual'), {
                    chart: { type: 'line', height: 260, toolbar: { show: false }, foreColor: '#c7d2fe' },
                    series: [
                        { name: 'OT', data: data.ordenes },
                        { name: 'Checklists', data: data.checklists },
                        { name: 'Solicitudes', data: data.solicitudes },
                    ],
                    xaxis: { categories: data.labels },
                    colors: ['#435ebe', '#39da8a', '#ffcf3f'],
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    tooltip: { y: { formatter: value => Number(value || 0).toLocaleString('es-AR') } },
                }).render();
            }

            lineMonthly();
            bar('chartOrdenesEstado', charts.ordenesPorEstado);
            bar('chartCostosRanking', charts.costosRanking, money);
            donut('chartEstadoFlota', charts.estadoFlota);
            donut('chartOrdenesTipo', charts.ordenesPorTipo);
            donut('chartSolicitudesEstado', charts.solicitudesPorEstado);
            donut('chartDisponibilidad', charts.disponibilidad);
            horizontalBar('chartCostosBase', charts.costosPorBase, money);
            horizontalBar('chartRankingFallas', charts.rankingFallas);
        });
    </script>
@endpush
