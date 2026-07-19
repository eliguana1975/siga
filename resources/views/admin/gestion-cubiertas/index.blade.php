@extends('layouts.admin')

@push('styles')
    <style>
        .tyre-metric {
            min-height: 118px;
        }

        .tyre-metric .metric-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: .5rem;
            font-size: 1.15rem;
            line-height: 1;
            text-align: center;
        }

        .tyre-metric .metric-icon i {
            display: block;
            line-height: 1;
        }

        .tyre-available-detail {
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            padding: 1rem;
        }

        .tyre-available-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem 1rem;
        }

        .tyre-available-label {
            display: block;
            color: var(--bs-secondary-color);
            font-size: .78rem;
            margin-bottom: .2rem;
        }

        .grid-column-full {
            grid-column: 1 / -1;
        }

        @media (max-width: 991.98px) {
            .tyre-available-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .tyre-available-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@php
    $estadoLabels = [
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En proceso',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
    ];

    $estadoBadges = [
        'pendiente' => 'bg-light-warning',
        'en_proceso' => 'bg-light-info',
        'completada' => 'bg-light-success',
        'cancelada' => 'bg-light-danger',
    ];

    $cubiertaEstadoBadges = [
        'nueva' => 'bg-light-primary',
        'reutilizable' => 'bg-light-success',
        'en_uso' => 'bg-light-info',
        'baja' => 'bg-light-danger',
    ];

    $canViewMovimientoCubiertas = auth()->user()?->can('movimiento-cubiertas.ver');
    $canEditMovimientoCubiertas = auth()->user()?->can('movimiento-cubiertas.editar');
    $showMovimientoCubiertasActions = $canViewMovimientoCubiertas || $canEditMovimientoCubiertas;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Gestion de Cubiertas</h3>
                <p class="text-subtitle text-muted">Consulta stock de cubiertas y ordenes de trabajo vinculadas a cambios de cubiertas.</p>
            </div>
            @can('movimiento-cubiertas.crear')
                <a href="{{ route('admin.movimiento-cubiertas.index') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo movimiento de cubiertas
                </a>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="row">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card tyre-metric">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Articulos cubiertas</p>
                                <h3 class="mb-0">{{ number_format($resumen['articulos_cubiertas'], 0, ',', '.') }}</h3>
                                <small class="text-muted">En catalogo de articulos</small>
                            </div>
                            <span class="metric-icon bg-light-primary"><i class="bi bi-circle"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card tyre-metric">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Stock cubiertas</p>
                                <h3 class="mb-0">{{ number_format($resumen['stock_cubiertas'], 0, ',', '.') }}</h3>
                                <small class="text-muted">Unidades disponibles</small>
                            </div>
                            <span class="metric-icon bg-light-info"><i class="bi bi-123"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card tyre-metric">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Ordenes cubiertas</p>
                                <h3 class="mb-0">{{ number_format($resumen['ordenes_cubiertas'], 0, ',', '.') }}</h3>
                                <small class="text-muted">{{ number_format($resumen['ordenes_pendientes'], 0, ',', '.') }} pendientes/en proceso</small>
                            </div>
                            <span class="metric-icon bg-light-warning"><i class="bi bi-clipboard-check"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card tyre-metric">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Completadas</p>
                                <h3 class="mb-0">{{ number_format($resumen['ordenes_completadas'], 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    Reutilizables: {{ number_format($resumen['cubiertas_reutilizables'], 0, ',', '.') }} /
                                    Baja: {{ number_format($resumen['cubiertas_baja'], 0, ',', '.') }}
                                </small>
                            </div>
                            <span class="metric-icon bg-light-success"><i class="bi bi-check2-circle"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="ultimasCubiertasGeneradas">
                <div class="card-header d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                    <div>
                        <h4 class="card-title mb-0">Ultimo ingreso de cubiertas</h4>
                        <small class="text-muted">Listado rapido para imprimir y marcar cubiertas del ultimo ingreso.</small>
                    </div>
                    <button type="button" class="btn btn-outline-primary" data-print-target="ultimasCubiertasGeneradas">
                        <i class="bi bi-printer"></i> Imprimir listado
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.gestion-cubiertas.index') }}" class="row g-3 align-items-end mb-3">
                        @if ($search !== '')
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        <div class="col-12 col-lg-7">
                            <label for="medida-reciente-articulo-id" class="form-label">Medida</label>
                            <select name="medida_reciente_articulo_id" id="medida-reciente-articulo-id" class="form-select js-select2" data-placeholder="Ultimo ingreso de todas las medidas">
                                <option value="">Ultimo ingreso de todas las medidas</option>
                                @foreach ($medidasCubiertasRecientes as $cubiertaMedida)
                                    <option value="{{ $cubiertaMedida->articulo_id }}" @selected((int) $medidaRecienteArticuloId === (int) $cubiertaMedida->articulo_id)>
                                        {{ $cubiertaMedida->articulo?->nombre ?? $cubiertaMedida->medida }}{{ $cubiertaMedida->medida ? ' - ' . $cubiertaMedida->medida : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Ver ultimo ingreso
                            </button>
                        </div>
                        <div class="col-12 col-lg-2">
                            <a href="{{ route('admin.gestion-cubiertas.index', $search !== '' ? ['search' => $search] : []) }}" class="btn btn-light-secondary w-100">Limpiar</a>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                @if ($ultimaEntradaCubiertas)
                                    Mostrando ingreso #{{ $ultimaEntradaCubiertas->entrada_id }} del {{ $ultimaEntradaCubiertas->fecha_ingreso?->format('d/m/Y') ?? $ultimaEntradaCubiertas->created_at?->format('d/m/Y') ?? '-' }}, ordenado de menor a mayor.
                                @else
                                    No hay ingresos de cubiertas para listar.
                                @endif
                            </small>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Marcar</th>
                                    <th>Nro.</th>
                                    <th>Articulo / medida</th>
                                    <th>Deposito</th>
                                    <th>Ingreso</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cubiertasRecientes as $cubierta)
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input" aria-label="Marcar cubierta {{ $cubierta->numero }}"></td>
                                        <td class="fw-semibold">{{ $cubierta->numero }}</td>
                                        <td>
                                            {{ $cubierta->articulo?->nombre ?? '-' }}
                                            <small class="d-block text-muted">{{ $cubierta->medida ?: ($cubierta->articulo?->codigo_producto ?: '-') }}</small>
                                        </td>
                                        <td>{{ $cubierta->deposito?->nombre ?? '-' }}</td>
                                        <td>
                                            @if ($cubierta->entrada_id)
                                                #{{ $cubierta->entrada_id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $cubierta->fecha_ingreso?->format('d/m/Y') ?? $cubierta->created_at?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $cubiertaEstadoBadges[$cubierta->estado] ?? 'bg-light-secondary' }}">
                                                {{ \App\Models\Cubierta::ESTADOS[$cubierta->estado] ?? ucfirst((string) $cubierta->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No hay cubiertas generadas recientemente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">El listado corresponde al ultimo ingreso encontrado y se ordena por numero de menor a mayor.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Cubiertas numeradas</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-12 col-lg-7">
                            <label for="cubiertaArticuloSelect" class="form-label">Cubierta</label>
                            <select id="cubiertaArticuloSelect" class="form-select js-select2" data-placeholder="Seleccione una cubierta">
                                <option value="">Seleccione una cubierta</option>
                                @foreach ($articulosCubiertasDisponibles as $articuloCubierta)
                                    <option value="{{ $articuloCubierta->id }}">
                                        {{ $articuloCubierta->nombre }}{{ $articuloCubierta->codigo_producto ? ' - ' . $articuloCubierta->codigo_producto : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccione una cubierta para ver sus numeros disponibles.</small>
                        </div>
                        <div class="col-12 col-lg-5 text-lg-end">
                            <small id="cubiertasDisponiblesInfo" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nro.</th>
                                    <th>Articulo / medida</th>
                                    <th>Estado</th>
                                    <th>Deposito</th>
                                    <th>Interno</th>
                                    <th>Posicion</th>
                                    <th>Ingreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cubiertasNumeradas as $cubierta)
                                    @php
                                        $medidaCubierta = $cubierta->medida ?: ($cubierta->articulo?->codigo_producto ?: $cubierta->articulo?->nombre);
                                    @endphp
                                    <tr data-cubierta-row data-articulo-id="{{ $cubierta->articulo_id }}">
                                        <td class="fw-semibold">{{ $cubierta->numero }}</td>
                                        <td>
                                            {{ $cubierta->articulo?->nombre ?? '-' }}
                                            <small class="d-block text-muted">{{ $cubierta->medida ?: ($cubierta->articulo?->codigo_producto ?: '-') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $cubiertaEstadoBadges[$cubierta->estado] ?? 'bg-light-secondary' }}">
                                                {{ \App\Models\Cubierta::ESTADOS[$cubierta->estado] ?? ucfirst((string) $cubierta->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $cubierta->deposito?->nombre ?? '-' }}</td>
                                        <td>
                                            {{ $cubierta->flota?->nro_interno ?? '-' }}
                                            @if ($cubierta->flota?->dominio)
                                                <small class="d-block text-muted">{{ $cubierta->flota->dominio }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $cubierta->posicion ?: '-' }}</td>
                                        <td>{{ $cubierta->fecha_ingreso?->format('d/m/Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No hay cubiertas numeradas para mostrar.</td>
                                    </tr>
                                @endforelse
                                <tr id="cubiertasDisponiblesEmpty" class="d-none">
                                    <td colspan="7" class="text-center text-muted py-4">Seleccione una cubierta para ver los numeros disponibles.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">El listado se completa con cubiertas disponibles, sin interno ni posicion.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Seguimiento de cubiertas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>OT</th>
                                    <th>Interno</th>
                                    <th>Posicion</th>
                                    <th>Cubierta colocada</th>
                                    <th>Cubierta retirada</th>
                                    <th>Estado retirada</th>
                                    <th>Destino</th>
                                    <th>Observacion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($seguimiento as $detalle)
                                    <tr>
                                        <td>{{ $detalle->cambioCubierta?->fecha?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            @if ($detalle->cambioCubierta?->orden_trabajo_id)
                                                #{{ $detalle->cambioCubierta->orden_trabajo_id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            {{ $detalle->cambioCubierta?->flota?->nro_interno ?? '-' }}
                                            @if ($detalle->cambioCubierta?->flota?->dominio)
                                                <small class="d-block text-muted">{{ $detalle->cambioCubierta->flota->dominio }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $detalle->posicion }}</td>
                                        <td>
                                            {{ $detalle->nro_cubierta_colocada ?: '-' }}
                                            @if ($detalle->articuloColocado)
                                                <small class="d-block text-muted">{{ $detalle->articuloColocado->nombre }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $detalle->nro_cubierta_sacada ?: '-' }}</td>
                                        <td>
                                            @if ($detalle->estado_cubierta_sacada === 'buena')
                                                <span class="badge bg-light-success">{{ $detalle->estadoCubiertaSacadaLabel() }}</span>
                                            @elseif ($detalle->estado_cubierta_sacada === 'baja')
                                                <span class="badge bg-light-danger">{{ $detalle->estadoCubiertaSacadaLabel() }}</span>
                                            @elseif ($detalle->estado_cubierta_sacada)
                                                <span class="badge bg-light-warning">{{ $detalle->estadoCubiertaSacadaLabel() }}</span>
                                            @else
                                                <span class="text-muted">Sin evaluar</span>
                                            @endif
                                        </td>
                                        <td>{{ $detalle->destinoCubiertaSacadaLabel() }}</td>
                                        <td>
                                            {{ $detalle->motivo_baja_cubierta_sacada ?: $detalle->observacion_cubierta_sacada ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No hay movimientos de cubiertas para mostrar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">Se muestran los ultimos 20 movimientos. Use el buscador para filtrar por numero de cubierta, interno o estado.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ordenes de trabajo por cambio de cubiertas</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible show fade">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.gestion-cubiertas.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar orden</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Orden, interno, dominio, creador, articulo o estado">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.ordenes-trabajo.index') }}" class="btn btn-light-secondary w-100"
                                    onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                                    Volver
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'ordenes_cambio_cubiertas')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'ordenes_cambio_cubiertas')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'ordenes_cambio_cubiertas')">
                                <i class="bi bi-file-earmark-pdf"></i> Crear PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="printTable('datatable')">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $ordenes->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    <th>Orden</th>
                                    <th>Fecha</th>
                                    <th>Interno</th>
                                    <th>Creador de la orden</th>
                                    <th>Base</th>
                                    <th>Cubiertas</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    @if ($showMovimientoCubiertasActions)
                                        <th class="text-end" style="width: 260px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ordenes as $orden)
                                    @php
                                        $detallesCubiertas = $orden->articulosUsados->filter(function ($detalle) {
                                            $categoria = strtolower((string) ($detalle->articulo?->categoria?->nombre ?? ''));
                                            $nombre = strtolower((string) ($detalle->articulo?->nombre ?? ''));
                                            $codigo = strtolower((string) ($detalle->articulo?->codigo_producto ?? ''));

                                            return str_contains($categoria, 'cubierta')
                                                || str_contains($nombre, 'cubierta')
                                                || str_contains($nombre, 'neumatic')
                                                || str_contains($codigo, 'cubierta')
                                                || str_contains($codigo, 'neumatic');
                                        });
                                        $cantidadCubiertas = $detallesCubiertas->sum('cantidad');
                                        $totalOrden = $detallesCubiertas->sum(fn ($detalle) => (float) $detalle->cantidad * (float) $detalle->valor_unitario);
                                    @endphp
                                    <tr>
                                        <td>{{ $ordenes->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="fw-semibold">OT #{{ $orden->id }}</div>
                                            <small class="text-muted">{{ $orden->titulo ?? 'Sin titulo' }}</small>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @forelse ($orden->motivos as $motivo)
                                                    <span class="badge bg-light-primary">{{ $motivo->nombre }}</span>
                                                @empty
                                                    <span class="text-muted small">Sin motivos</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>{{ optional($orden->fecha_orden)->format('d/m/Y') ?? 'N/A' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $orden->flota?->nro_interno ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $orden->flota?->dominio ?? 'Sin dominio' }}</small>
                                        </td>
                                        <td>{{ trim(($orden->empleado?->apellidos ?? '') . ', ' . ($orden->empleado?->nombres ?? '')) ?: 'N/A' }}</td>
                                        <td>{{ $orden->base?->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ number_format($cantidadCubiertas, 0, ',', '.') }} unidad(es)</div>
                                            <small class="text-muted">
                                                {{ $detallesCubiertas->pluck('articulo.nombre')->filter()->unique()->take(2)->implode(' / ') ?: 'Sin articulo de cubierta' }}
                                            </small>
                                        </td>
                                        <td>${{ number_format($totalOrden, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$orden->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$orden->estado] ?? $orden->estado }}
                                            </span>
                                        </td>
                                        @if ($showMovimientoCubiertasActions)
                                        <td class="text-end">
                                            @if ($canViewMovimientoCubiertas)
                                            <a href="{{ route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id]) }}"
                                                class="btn btn-sm btn-info{{ $cantidadCubiertas > 0 ? ' disabled' : '' }}"
                                                title="Ver croquis"{{ $cantidadCubiertas > 0 ? ' aria-disabled="true" tabindex="-1"' : '' }}>
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif
                                            @if ($canEditMovimientoCubiertas)
                                            <a href="{{ route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id, 'edit' => 1]) }}"
                                                class="btn btn-sm btn-warning" title="Editar croquis">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @endif
                                            @if ($canViewMovimientoCubiertas)
                                            <a href="{{ route('admin.movimiento-cubiertas.index', ['orden_trabajo_id' => $orden->id, 'print' => 1]) }}"
                                                class="btn btn-sm btn-secondary" title="Imprimir croquis">
                                                <i class="bi bi-printer"></i> Imprimir
                                            </a>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showMovimientoCubiertasActions ? 10 : 9 }}" class="text-center text-muted py-4">No hay ordenes de cambio de cubiertas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($ordenes->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $ordenes->firstItem() }} a {{ $ordenes->lastItem() }} de
                                {{ $ordenes->total() }} registros
                            </small>
                            <div>
                                {{ $ordenes->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('cubiertaArticuloSelect');
            const rows = document.querySelectorAll('[data-cubierta-row]');
            const emptyRow = document.getElementById('cubiertasDisponiblesEmpty');
            const info = document.getElementById('cubiertasDisponiblesInfo');

            function updateAvailableTyresByArticle() {
                const selectedArticleId = String(select?.value || '');
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = selectedArticleId !== '' && row.dataset.articuloId === selectedArticleId;

                    row.classList.toggle('d-none', !visible);

                    if (visible) {
                        visibleCount += 1;
                    }
                });

                emptyRow?.classList.toggle('d-none', visibleCount > 0);

                if (info) {
                    info.textContent = selectedArticleId === ''
                        ? 'Seleccione una cubierta'
                        : `${visibleCount} cubierta(s) disponible(s)`;
                }
            }

            select?.addEventListener('change', updateAvailableTyresByArticle);

            if (window.jQuery) {
                window.jQuery(select).on('select2:select select2:clear change', updateAvailableTyresByArticle);
            }

            updateAvailableTyresByArticle();

            document.querySelectorAll('[data-print-target]').forEach((button) => {
                button.addEventListener('click', function () {
                    const target = document.getElementById(this.dataset.printTarget);

                    if (! target) {
                        return;
                    }

                    const printWindow = window.open('', '_blank', 'width=1000,height=700');

                    if (! printWindow) {
                        return;
                    }

                    printWindow.document.write(`
                        <!doctype html>
                        <html>
                            <head>
                                <title>Ultimas cubiertas generadas</title>
                                <style>
                                    body { font-family: Arial, sans-serif; color: #111; margin: 24px; }
                                    h4 { margin: 0 0 4px; font-size: 18px; }
                                    small { color: #555; }
                                    button { display: none; }
                                    table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 12px; }
                                    th, td { border: 1px solid #999; padding: 7px; text-align: left; vertical-align: top; }
                                    th { background: #eee; }
                                    .badge { border: 1px solid #999; padding: 2px 6px; border-radius: 4px; }
                                    input[type="checkbox"] { width: 14px; height: 14px; }
                                </style>
                            </head>
                            <body>${target.innerHTML}</body>
                        </html>
                    `);
                    printWindow.document.close();
                    printWindow.focus();
                    printWindow.print();
                });
            });
        });
    </script>
@endpush
