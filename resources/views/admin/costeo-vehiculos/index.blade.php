@extends('layouts.admin')

@push('styles')
    <style>
        .vehicle-cost-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
        }

        .vehicle-cost-scroll {
            max-height: 720px;
            overflow-y: auto;
            padding-right: .35rem;
        }

        .vehicle-cost-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .vehicle-cost-scroll::-webkit-scrollbar-thumb {
            background: rgba(151, 164, 255, .45);
            border-radius: 999px;
        }

        .vehicle-cost-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .05);
            border-radius: 999px;
        }

        .vehicle-cost-chart {
            min-height: 180px;
        }

        .vehicle-cost-card .card-body {
            min-height: 200px;
        }

        .vehicle-cost-card .card-header,
        .vehicle-cost-card .card-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .vehicle-cost-card .card-title {
            font-size: .74rem !important;
            line-height: 1.05;
            font-weight: 700;
        }

        .vehicle-cost-card .badge {
            font-size: .68rem !important;
            line-height: 1;
            padding: .28rem .38rem;
            white-space: nowrap;
        }

        .vehicle-cost-card small {
            font-size: .66rem !important;
            line-height: 1.2;
        }

        .vehicle-cost-section-title {
            font-size: .86rem !important;
            line-height: 1.1;
        }

        .vehicle-cost-section-subtitle {
            font-size: .76rem !important;
            line-height: 1.15;
        }

        @media (max-width: 1199.98px) {
            .vehicle-cost-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .vehicle-cost-grid {
                grid-template-columns: 1fr;
            }

            .vehicle-cost-scroll {
                max-height: none;
                overflow-y: visible;
                padding-right: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3>Costeo por vehiculo</h3>
                <p class="text-subtitle text-muted">Ranking de unidades por consumo real de repuestos y cubiertas imputados a OT.</p>
            </div>
            <a href="{{ route('admin.historial-articulos-vehiculo.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-clock-history"></i> Historial
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.costeo-vehiculos.index') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $desde->format('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $hasta->format('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.costeo-vehiculos.index') }}" class="btn btn-light-secondary">Mes actual</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total periodo</h6>
                            <h3 class="mb-0">$ {{ number_format($costeo['totales']['total'], 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Repuestos</h6>
                            <h3 class="mb-0">$ {{ number_format($costeo['totales']['repuestos'], 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Cubiertas</h6>
                            <h3 class="mb-0">$ {{ number_format($costeo['totales']['cubiertas'], 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Unidades con costo</h6>
                            <h3 class="mb-0">{{ number_format($costeo['totales']['vehiculos_con_costo'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div>
                        <h4 class="mb-0 vehicle-cost-section-title">Costos por categoria y vehiculo</h4>
                        <p class="text-subtitle text-muted mb-0 vehicle-cost-section-subtitle">Cada tarjeta se actualiza con el filtro de fechas seleccionado.</p>
                    </div>
                </div>

                <div class="vehicle-cost-scroll">
                    <div class="vehicle-cost-grid">
                        @forelse ($costeo['graficos'] as $vehiculo)
                            <div class="card mb-0 vehicle-cost-card">
                                <div class="card-header pb-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <h5 class="card-title mb-1 text-truncate">{{ $vehiculo['interno'] }} - {{ $vehiculo['dominio'] }}</h5>
                                            <small class="text-muted d-block text-truncate">{{ $vehiculo['vehiculo'] ?: 'Vehiculo sin tipo' }}</small>
                                        </div>
                                        <span class="badge bg-light-primary">
                                            $ {{ number_format($vehiculo['total'], 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if ($vehiculo['total'] > 0)
                                        <div id="vehicle-cost-chart-{{ $vehiculo['flota_id'] }}" class="vehicle-cost-chart"></div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center text-muted border rounded vehicle-cost-chart">
                                            Sin costos en el periodo
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="card mb-0">
                                <div class="card-body text-center text-muted py-4">
                                    No hay vehiculos cargados.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ranking de unidades mas caras</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Interno</th>
                                    <th>Dominio</th>
                                    <th>Vehiculo</th>
                                    <th class="text-end">Repuestos</th>
                                    <th class="text-end">Cubiertas</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Km periodo</th>
                                    <th class="text-end">Costo/km</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($costeo['ranking'] as $row)
                                    <tr>
                                        <td class="fw-semibold">{{ $row['interno'] }}</td>
                                        <td>{{ $row['dominio'] }}</td>
                                        <td>{{ $row['vehiculo'] ?: '-' }}</td>
                                        <td class="text-end">$ {{ number_format($row['repuestos'], 2, ',', '.') }}</td>
                                        <td class="text-end">$ {{ number_format($row['cubiertas'], 2, ',', '.') }}</td>
                                        <td class="text-end fw-semibold">$ {{ number_format($row['total'], 2, ',', '.') }}</td>
                                        <td class="text-end">{{ $row['km_periodo'] > 0 ? number_format($row['km_periodo'], 0, ',', '.') : '-' }}</td>
                                        <td class="text-end">
                                            {{ $row['costo_por_km'] !== null ? '$ ' . number_format($row['costo_por_km'], 2, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No hay costos imputados a vehiculos en el periodo seleccionado.
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof ApexCharts === 'undefined') {
                    return;
                }

                const palette = ['#435ebe', '#00cfe8', '#39da8a', '#ffcf3f', '#ff5b5c', '#9694ff', '#fdac41'];
                const charts = @json(collect($costeo['graficos'])->filter(fn ($vehiculo) => $vehiculo['total'] > 0)->values());

                charts.forEach(function (vehiculo) {
                    const element = document.getElementById('vehicle-cost-chart-' + vehiculo.flota_id);

                    if (!element) {
                        return;
                    }

                    new ApexCharts(element, {
                        chart: {
                            type: 'donut',
                            height: 180,
                            toolbar: { show: false },
                            foreColor: '#c7d2fe',
                        },
                        labels: vehiculo.categorias.map(function (categoria) {
                            return categoria.categoria;
                        }),
                        series: vehiculo.categorias.map(function (categoria) {
                            return Number(categoria.total);
                        }),
                        colors: palette,
                        stroke: {
                            width: 2,
                            colors: ['#1f1d2f'],
                        },
                        dataLabels: {
                            enabled: false,
                        },
                        legend: {
                            show: false,
                        },
                        tooltip: {
                            y: {
                                formatter: function (value) {
                                    return '$ ' + Number(value).toLocaleString('es-AR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    });
                                },
                            },
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '68%',
                                    labels: {
                                        show: true,
                                        name: {
                                            fontSize: '12px',
                                            fontWeight: 500,
                                        },
                                        value: {
                                            fontSize: '14px',
                                            fontWeight: 600,
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            fontSize: '12px',
                                            fontWeight: 500,
                                            formatter: function () {
                                                return '$ ' + Number(vehiculo.total).toLocaleString('es-AR', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                });
                                            },
                                        },
                                    },
                                },
                            },
                        },
                    }).render();
                });
            });
        </script>
    @endpush
@endsection
