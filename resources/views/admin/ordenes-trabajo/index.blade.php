@extends('layouts.admin')

@php
    $isSuperUsuario = auth()->user()?->isSuperUsuario();

    $tipoTrabajoLabels = [
        'preventivo' => 'Preventivo',
        'correctivo' => 'Correctivo',
        'inspeccion' => 'Inspeccion',
        'reparacion' => 'Reparacion',
    ];

    $prioridadLabels = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

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
        'cancelada' => 'bg-light-secondary',
    ];

    $prioridadBadges = [
        'baja' => 'bg-light-secondary',
        'media' => 'bg-light-info',
        'alta' => 'bg-light-warning',
        'urgente' => 'bg-light-danger',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Ordenes de trabajo</h3>
                <p class="text-subtitle text-muted">Administra trabajos asociados a empleados y vehiculos de la flota.</p>
            </div>
            @can('ordenes-trabajo.crear')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrdenTrabajoModal">
                    <i class="bi bi-plus-circle"></i> Nueva orden
                </button>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de ordenes</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.ordenes-trabajo.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar orden</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nro. orden, titulo, motivo, empleado, actualizador, base, kilometraje, interno, dominio, estado...">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.ordenes-trabajo.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'ordenes_trabajo')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'ordenes_trabajo')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'ordenes_trabajo')">
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
                                    <th>Fecha</th>
                                    <th>Titulo / Origen</th>
                                    <th>Motivos</th>
                                    <th>Creador de la orden</th>
                                    <th>Ultima actualizacion</th>
                                    <th>Flota</th>
                                    <th>Kilometraje</th>
                                    <th>Base</th>
                                    <th>Tipo</th>
                                    <th>Prioridad</th>
                                    <th>Parado</th>
                                    <th>Estado</th>
                                    <th class="text-end" style="width: 260px;">Cantidad / Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ordenes as $orden)
                                    @php
                                        $ordenCerrada = $orden->estaCerrada();
                                    @endphp
                                    <tr>
                                        <td>{{ $orden->id }}</td>
                                        <td>{{ optional($orden->fecha_orden)->format('d/m/Y') }}</td>
                                        <td>{{ $orden->titulo }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse ($orden->motivos as $motivo)
                                                    <span class="badge bg-light-primary">{{ $motivo->nombre }}</span>
                                                @empty
                                                    <span class="text-muted">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>{{ $orden->empleado?->apellidos }}, {{ $orden->empleado?->nombres }}</td>
                                        <td>
                                            <div>{{ $orden->actualizadoPor?->name ?? '-' }}</div>
                                            <small class="text-muted">{{ optional($orden->updated_at)->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>{{ $orden->flota?->nro_interno }} - {{ $orden->flota?->dominio }}</td>
                                        <td>{{ $orden->kilometraje !== null ? number_format($orden->kilometraje, 0, ',', '.') . ' km' : '' }}</td>
                                        <td>{{ $orden->base?->nombre }}</td>
                                        <td>{{ $tipoTrabajoLabels[$orden->tipo_trabajo] ?? $orden->tipo_trabajo }}</td>
                                        <td>
                                            <span class="badge {{ $prioridadBadges[$orden->prioridad] ?? 'bg-light-secondary' }}">
                                                {{ $prioridadLabels[$orden->prioridad] ?? $orden->prioridad }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($orden->vehiculo_parado)
                                                <span class="badge bg-light-danger">
                                                    {{ $motivoVehiculoParadoLabels[$orden->motivo_vehiculo_parado] ?? 'Parado' }}
                                                </span>
                                            @else
                                                <span class="badge bg-light-success">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$orden->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$orden->estado] ?? $orden->estado }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex flex-column align-items-end gap-1">
                                                <span class="badge bg-light-primary">
                                                    {{ (int) ($orden->articulos_usados_sum_cantidad ?? $orden->articulosUsados->sum('cantidad')) }} unidad(es)
                                                </span>
                                                <div class="d-flex justify-content-end gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                        data-bs-target="#showOrdenTrabajoModal-{{ $orden->id }}"
                                                        title="Ver orden">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="printOrdenTrabajo('printOrdenTrabajo-{{ $orden->id }}')"
                                                        title="Imprimir orden">
                                                        <i class="bi bi-printer"></i>
                                                    </button>
                                                    @canany(['ordenes-trabajo-articulos.agregar', 'ordenes-trabajo-articulos.quitar'])
                                                        <a href="{{ route('admin.ordenes-trabajo.articulos', $orden->id) }}" class="btn btn-sm btn-info"
                                                            title="Articulos usados">
                                                            <i class="bi bi-box-seam"></i>
                                                        </a>
                                                    @endcanany
                                                    @can('solicitudes-repuestos.crear')
                                                        @unless ($orden->estaCerrada())
                                                            <a href="{{ route('admin.solicitudes-repuestos.create', ['orden_trabajo_id' => $orden->id, 'flota_id' => $orden->flota_id]) }}"
                                                                class="btn btn-sm btn-warning" title="Pedir repuesto">
                                                                <i class="bi bi-tools"></i>
                                                            </a>
                                                        @endunless
                                                    @endcan
                                                    @if ($isSuperUsuario)
                                                        <a href="{{ route('admin.ordenes-trabajo.edit', $orden->id) }}" class="btn btn-sm btn-success"
                                                            title="Editar orden">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteOrdenTrabajoModal-{{ $orden->id }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center text-muted py-4">
                                            No hay ordenes de trabajo registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($ordenes->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $ordenes->firstItem() }} a {{ $ordenes->lastItem() }} de {{ $ordenes->total() }} registros
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

    @can('ordenes-trabajo.crear')
    <div class="modal fade" id="createOrdenTrabajoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.ordenes-trabajo.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Nueva orden de trabajo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'createOrdenTrabajoModal')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        @include('admin.ordenes-trabajo.partials.form-fields', [
                            'orden' => null,
                            'modalId' => 'createOrdenTrabajoModal',
                            'tipoTrabajoLabels' => $tipoTrabajoLabels,
                            'prioridadLabels' => $prioridadLabels,
                            'estadoLabels' => $estadoLabels,
                            'motivosOrdenTrabajo' => $motivosOrdenTrabajo,
                            'motivoVehiculoParadoLabels' => $motivoVehiculoParadoLabels,
                        ])
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    @foreach ($ordenes as $orden)
        @php
            $ordenCerrada = $orden->estaCerrada();
        @endphp

        <div class="modal fade" id="showOrdenTrabajoModal-{{ $orden->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" style="color: white">Orden de trabajo #{{ $orden->id }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <h5 class="mb-1">{{ $orden->titulo }}</h5>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @forelse ($orden->motivos as $motivo)
                                        <span class="badge bg-light-primary">{{ $motivo->nombre }}</span>
                                    @empty
                                        <span class="text-muted small">Sin motivos cargados</span>
                                    @endforelse
                                </div>
                                <div class="text-muted mb-0">
                                    <div><strong>Motivos:</strong> {{ $orden->motivos->pluck('nombre')->join(', ') ?: '-' }}</div>
                                    <div>{{ $orden->descripcion ?: 'Sin descripcion.' }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 text-lg-end">
                                <span class="badge {{ $estadoBadges[$orden->estado] ?? 'bg-light-secondary' }}">
                                    {{ $estadoLabels[$orden->estado] ?? $orden->estado }}
                                </span>
                                <span class="badge {{ $prioridadBadges[$orden->prioridad] ?? 'bg-light-secondary' }}">
                                    {{ $prioridadLabels[$orden->prioridad] ?? $orden->prioridad }}
                                </span>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 180px;">Fecha</th>
                                                <td>{{ optional($orden->fecha_orden)->format('d/m/Y') }}</td>
                                                <th style="width: 180px;">Fecha cierre</th>
                                                <td>{{ optional($orden->fecha_cierre)->format('d/m/Y') ?? 'Pendiente' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Empleado</th>
                                                <td>{{ $orden->empleado?->apellidos }}, {{ $orden->empleado?->nombres }}</td>
                                                <th>Reparador</th>
                                                <td>{{ $orden->reparador?->apellidos }}, {{ $orden->reparador?->nombres }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ultima actualizacion</th>
                                                <td>{{ $orden->actualizadoPor?->name ?? '-' }}</td>
                                                <th>Fecha actualizacion</th>
                                                <td>{{ optional($orden->updated_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Flota</th>
                                                <td>{{ $orden->flota?->nro_interno }} - {{ $orden->flota?->dominio }}</td>
                                                <th>Kilometraje</th>
                                                <td>{{ $orden->kilometraje !== null ? number_format($orden->kilometraje, 0, ',', '.') . ' km' : 'Sin informar' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Base</th>
                                                <td>{{ $orden->base?->nombre }}</td>
                                                <th>Tipo de trabajo</th>
                                                <td>{{ $tipoTrabajoLabels[$orden->tipo_trabajo] ?? $orden->tipo_trabajo }}</td>
                                            </tr>
                                            <tr>
                                                <th>Vehiculo parado</th>
                                                <td>{{ $orden->vehiculo_parado ? 'Si' : 'No' }}</td>
                                                <th>Motivo</th>
                                                <td>{{ $orden->vehiculo_parado ? ($motivoVehiculoParadoLabels[$orden->motivo_vehiculo_parado] ?? '-') : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Parado desde</th>
                                                <td>{{ optional($orden->fecha_vehiculo_parado)->format('d/m/Y') ?? '-' }}</td>
                                                <th>Observacion parada</th>
                                                <td>{{ $orden->observacion_vehiculo_parado ?: '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Motivos</th>
                                                <td colspan="3">
                                                    @forelse ($orden->motivos as $motivo)
                                                        <span class="badge bg-light-primary">{{ $motivo->nombre }}</span>
                                                    @empty
                                                        -
                                                    @endforelse
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Observaciones</th>
                                                <td colspan="3">{{ $orden->observaciones ?: '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Articulos usados</h6>
                                    <span class="badge bg-light-primary">
                                        {{ (int) ($orden->articulos_usados_sum_cantidad ?? $orden->articulosUsados->sum('cantidad')) }} unidad(es)
                                    </span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Articulo</th>
                                                <th>Codigo</th>
                                                <th>Unidad</th>
                                                <th>Cantidad</th>
                                                <th>Valor unit.</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($orden->articulosUsados as $detalle)
                                                <tr>
                                                    <td>{{ $detalle->articulo?->nombre }}</td>
                                                    <td>{{ $detalle->articulo?->codigo_producto }}</td>
                                                    <td>{{ $detalle->articulo?->unidadMedida?->nombre }}</td>
                                                    <td>{{ $detalle->cantidad }}</td>
                                                    <td>${{ number_format((float) $detalle->valor_unitario, 2, ',', '.') }}</td>
                                                    <td>${{ number_format((float) $detalle->valor_unitario * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">Sin articulos cargados.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @can('solicitudes-repuestos.crear')
                            @unless ($ordenCerrada)
                                <a href="{{ route('admin.solicitudes-repuestos.create', ['orden_trabajo_id' => $orden->id, 'flota_id' => $orden->flota_id]) }}"
                                    class="btn btn-warning">
                                    <i class="bi bi-tools"></i> Pedir repuesto
                                </a>
                            @endunless
                        @endcan
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="printOrdenTrabajo('printOrdenTrabajo-{{ $orden->id }}')">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="printOrdenTrabajo-{{ $orden->id }}" class="d-none">
            <div class="print-order">
                <div class="print-company">
                    @if ($empresa['logo'])
                        <img src="{{ $empresa['logo'] }}" alt="{{ $empresa['nombre'] }}">
                    @endif
                    <div>
                        <h3>{{ $empresa['nombre'] }}</h3>
                        @if ($empresa['descripcion'])
                            <p>{{ $empresa['descripcion'] }}</p>
                        @endif
                        <p>
                            {{ collect([$empresa['direccion'], $empresa['localidad']])->filter()->implode(' - ') }}
                            @if ($empresa['telefono'])
                                | Tel: {{ $empresa['telefono'] }}
                            @endif
                            @if ($empresa['email'])
                                | {{ $empresa['email'] }}
                            @endif
                            @if ($empresa['web'])
                                | {{ $empresa['web'] }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="print-order-header">
                    <div>
                        <h1>Orden de trabajo #{{ $orden->id }}</h1>
                        <p>{{ $orden->titulo }}</p>
                        <p>Motivos: {{ $orden->motivos->pluck('nombre')->join(', ') ?: '-' }}</p>
                    </div>
                    <div class="print-order-status">
                        <strong>{{ $estadoLabels[$orden->estado] ?? $orden->estado }}</strong>
                    </div>
                </div>

                <table class="print-order-meta">
                    <tbody>
                        <tr>
                            <th>Fecha</th>
                            <td>{{ optional($orden->fecha_orden)->format('d/m/Y') }}</td>
                            <th>Prioridad</th>
                            <td>{{ $prioridadLabels[$orden->prioridad] ?? $orden->prioridad }}</td>
                        </tr>
                        <tr>
                            <th>Tipo</th>
                            <td>{{ $tipoTrabajoLabels[$orden->tipo_trabajo] ?? $orden->tipo_trabajo }}</td>
                            <th>Base</th>
                            <td>{{ $orden->base?->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Motivos</th>
                            <td colspan="3">{{ $orden->motivos->pluck('nombre')->join(', ') ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Empleado</th>
                            <td>{{ $orden->empleado?->apellidos }}, {{ $orden->empleado?->nombres }}</td>
                            <th>Flota</th>
                            <td>{{ $orden->flota?->nro_interno }} - {{ $orden->flota?->dominio }}</td>
                        </tr>
                        <tr>
                            <th>Ultima actualizacion</th>
                            <td>{{ $orden->actualizadoPor?->name ?? '-' }}</td>
                            <th>Fecha actualizacion</th>
                            <td>{{ optional($orden->updated_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kilometraje</th>
                            <td>{{ $orden->kilometraje !== null ? number_format($orden->kilometraje, 0, ',', '.') . ' km' : 'Sin informar' }}</td>
                            <th>Fecha cierre</th>
                            <td>{{ optional($orden->fecha_cierre)->format('d/m/Y') ?? 'Pendiente' }}</td>
                        </tr>
                        <tr>
                            <th>Vehiculo parado</th>
                            <td>{{ $orden->vehiculo_parado ? 'Si' : 'No' }}</td>
                            <th>Motivo</th>
                            <td>{{ $orden->vehiculo_parado ? ($motivoVehiculoParadoLabels[$orden->motivo_vehiculo_parado] ?? '-') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Parado desde</th>
                            <td>{{ optional($orden->fecha_vehiculo_parado)->format('d/m/Y') ?? '-' }}</td>
                            <th>Observacion parada</th>
                            <td>{{ $orden->observacion_vehiculo_parado ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                <section>
                    <h2>Descripcion</h2>
                    <div class="print-order-box print-order-description">
                        @php
                            $descriptionLines = collect(preg_split('/\r\n|\r|\n/', (string) $orden->descripcion))
                                ->map(fn ($line) => trim($line))
                                ->filter()
                                ->values();

                            $descriptionTitle = $descriptionLines->first(fn ($line) => !str_starts_with($line, '-') && !str_contains($line, ':'));
                            $groupedDescription = $descriptionLines
                                ->filter(fn ($line) => str_starts_with($line, '-') && str_contains($line, ':'))
                                ->map(function ($line) {
                                    $line = trim(ltrim($line, '- '));
                                    [$group, $item] = array_pad(explode(':', $line, 2), 2, '');

                                    return [
                                        'group' => trim($group),
                                        'item' => trim($item),
                                    ];
                                })
                                ->filter(fn ($line) => $line['group'] !== '' && $line['item'] !== '')
                                ->groupBy('group');

                            $plainDescription = $descriptionLines
                                ->reject(fn ($line) => !str_starts_with($line, '-') && !str_contains($line, ':'))
                                ->reject(fn ($line) => str_starts_with($line, '-') && str_contains($line, ':'))
                                ->values();
                        @endphp

                        @if ($descriptionTitle)
                            <strong>{{ $descriptionTitle }}</strong>
                        @endif

                        <div class="print-order-description-group">
                            <b>Motivos:</b>
                            <span>{{ $orden->motivos->pluck('nombre')->join(', ') ?: '-' }}</span>
                        </div>

                        @forelse ($groupedDescription as $group => $items)
                            <div class="print-order-description-group">
                                <b>{{ $group }}:</b>
                                <span>{{ $items->pluck('item')->join(' - ') }}</span>
                            </div>
                        @empty
                            @foreach ($plainDescription as $line)
                                <span>{{ str_starts_with($line, '-') ? $line : '- ' . $line }}</span>
                            @endforeach
                        @endforelse

                        @if ($descriptionLines->isEmpty())
                            <div>Sin descripcion</div>
                        @endif
                    </div>
                </section>

                <div class="print-order-signatures">
                    <div>Solicitante</div>
                    <div>Responsable</div>
                    <div>Conformidad</div>
                </div>
            </div>
        </div>

        @if ($isSuperUsuario)
        <div class="modal fade" id="editOrdenTrabajoModal-{{ $orden->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.ordenes-trabajo.update', $orden->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar orden de trabajo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any() && session('open_modal') === 'editOrdenTrabajoModal-' . $orden->id)
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            @include('admin.ordenes-trabajo.partials.form-fields', [
                                'orden' => $orden,
                                'modalId' => 'editOrdenTrabajoModal-' . $orden->id,
                                'tipoTrabajoLabels' => $tipoTrabajoLabels,
                                'prioridadLabels' => $prioridadLabels,
                                'estadoLabels' => $estadoLabels,
                                'motivosOrdenTrabajo' => $motivosOrdenTrabajo,
                                'motivoVehiculoParadoLabels' => $motivoVehiculoParadoLabels,
                            ])
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if ($isSuperUsuario)
        <div class="modal fade" id="deleteOrdenTrabajoModal-{{ $orden->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.ordenes-trabajo.destroy', $orden->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar orden de trabajo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">
                            Esta seguro de eliminar la orden <strong>{{ $orden->titulo }}</strong>?
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="modal fade" id="articulosOrdenTrabajoModal-{{ $orden->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" style="color: white">Articulos usados - {{ $orden->titulo }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any() && session('open_modal') === 'articulosOrdenTrabajoModal-' . $orden->id)
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($ordenCerrada)
                            <div class="alert alert-info">
                                Esta orden de trabajo esta cerrada. Los articulos quedan solo para consulta.
                            </div>
                        @endif

                        @unless ($ordenCerrada)
                        <form method="POST" action="{{ route('admin.ordenes-trabajo.articulos.store', $orden->id) }}" class="mb-4">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-7">
                                    <label class="form-label">Articulo (*)</label>
                                    <select name="articulo_id" class="form-select js-select2" data-icon-decorated="true" data-placeholder="Escriba para buscar articulo" required>
                                        <option value="">Seleccione articulo</option>
                                        @foreach ($articulos as $articulo)
                                            @php
                                                $articuloLabel = $articulo->nombre . ($articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '');
                                            @endphp
                                            <option value="{{ $articulo->id }}" @selected((string) old('articulo_id') === (string) $articulo->id)>
                                                {{ $articuloLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cantidad (*)</label>
                                    <input type="number" name="cantidad" class="form-control" value="{{ old('cantidad', 1) }}" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Agregar articulo
                                    </button>
                                </div>
                            </div>
                        </form>
                        @endunless

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Articulos cargados</h6>
                            <div class="d-flex gap-2">
                                <span class="badge bg-light-info">
                                    {{ (int) ($orden->articulos_usados_count ?? $orden->articulosUsados->count()) }} item(s) / {{ (int) ($orden->articulos_usados_sum_cantidad ?? $orden->articulosUsados->sum('cantidad')) }} unidad(es)
                                </span>
                                <span class="badge bg-light-success">
                                    ${{ number_format($orden->articulosUsados->sum(fn ($detalle) => (float) $detalle->valor_unitario * (int) $detalle->cantidad), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 280px; width: 38%;">Articulo</th>
                                        <th style="width: 120px;">Codigo</th>
                                        <th style="width: 130px;">Unidad</th>
                                        <th style="width: 110px;">Cantidad</th>
                                        <th style="width: 140px;">Valor unit.</th>
                                        <th style="width: 140px;">Total</th>
                                        @unless ($ordenCerrada)
                                            <th class="text-end" style="width: 90px;">Acciones</th>
                                        @endunless
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($orden->articulosUsados as $detalle)
                                        <tr>
                                            <td>{{ $detalle->articulo?->nombre }}</td>
                                            <td>{{ $detalle->articulo?->codigo_producto }}</td>
                                            <td>{{ $detalle->articulo?->unidadMedida?->nombre }}</td>
                                            <td>{{ $detalle->cantidad }}</td>
                                            <td>${{ number_format((float) $detalle->valor_unitario, 2, ',', '.') }}</td>
                                            <td>${{ number_format((float) $detalle->valor_unitario * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                            @unless ($ordenCerrada)
                                                <td class="text-end">
                                                    <form method="POST" action="{{ route('admin.ordenes-trabajo.articulos.destroy', [$orden->id, $detalle->id]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endunless
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $ordenCerrada ? 6 : 7 }}" class="text-center text-muted py-4">
                                                No hay articulos cargados para esta orden.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script data-open-modal="{{ session('open_modal') }}">
        (function() {
            const openModalId = document.currentScript.dataset.openModal;

            if (!openModalId || typeof bootstrap === 'undefined') {
                return;
            }

            const modalElement = document.getElementById(openModalId);

            if (!modalElement) {
                return;
            }

            new bootstrap.Modal(modalElement).show();
        })();

        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadCSVFromTable(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const csvRows = [];

            const headers = Array.from(table.querySelectorAll('thead th')).map(header => '"' + header.innerText.trim().replace(/"/g, '""') + '"');
            csvRows.push(headers.join(','));

            rows.forEach(row => {
                const cols = Array.from(row.querySelectorAll('td')).map(td => '"' + td.innerText.trim().replace(/"/g, '""') + '"');
                csvRows.push(cols.join(','));
            });

            downloadCSV(csvRows.join('\n'), filename);
        }

        function exportTableToExcel(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8" /></head><body>${table.outerHTML}</body></html>`;
            const uri = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.href = uri;
            link.download = filename + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function createPDF(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}h1{text-align:center;margin-bottom:1rem;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>' + filename + '</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write('<h1>' + filename.replace(/_/g, ' ') + '</h1>');
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

        function printTable(tableId) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Imprimir</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

        function printOrdenTrabajo(templateId) {
            const template = document.getElementById(templateId);
            if (!template) {
                return;
            }

            const style = `
                <style>
                    * { box-sizing: border-box; }
                    body {
                        margin: 0;
                        padding: 12px;
                        color: #20222f;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 11px;
                        line-height: 1.18;
                    }
                    .print-order-header {
                        display: flex;
                        align-items: flex-start;
                        justify-content: space-between;
                        gap: 12px;
                        padding-bottom: 8px;
                        border-bottom: 2px solid #435ebe;
                        margin-bottom: 8px;
                    }
                    .print-company {
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        padding-bottom: 8px;
                        margin-bottom: 8px;
                        border-bottom: 1px solid #d8dff5;
                    }
                    .print-company img {
                        width: 54px;
                        height: 54px;
                        object-fit: contain;
                    }
                    .print-company h3 {
                        margin: 0 0 2px;
                        font-size: 15px;
                        color: #25396f;
                    }
                    .print-company p {
                        margin: 0 0 2px;
                        color: #4b5563;
                        font-size: 10px;
                        line-height: 1.3;
                    }
                    h1 {
                        margin: 0 0 3px;
                        font-size: 18px;
                        color: #25396f;
                    }
                    h2 {
                        margin: 8px 0 4px;
                        font-size: 12px;
                        color: #25396f;
                    }
                    p { margin: 0; }
                    .print-order-status {
                        min-width: 105px;
                        padding: 5px 8px;
                        border: 1px solid #cbd3ef;
                        border-radius: 4px;
                        text-align: center;
                        color: #25396f;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .print-order-meta th,
                    .print-order-meta td,
                    .print-order-table th,
                    .print-order-table td {
                        border: 1px solid #dee2e6;
                        padding: 4px 5px;
                        vertical-align: top;
                        text-align: left;
                    }
                    .print-order-meta th,
                    .print-order-table th {
                        background: #f2f4fb;
                        color: #25396f;
                        font-weight: 700;
                    }
                    .print-order-meta th {
                        width: 16%;
                    }
                    .print-order-box {
                        min-height: 0;
                        padding: 6px;
                        border: 1px solid #dee2e6;
                        border-radius: 4px;
                        white-space: normal;
                    }
                    .print-order-description {
                        display: flex;
                        flex-direction: column;
                        gap: 2px;
                        align-items: baseline;
                    }
                    .print-order-description strong {
                        margin-bottom: 1px;
                    }
                    .print-order-description-group {
                        display: flex;
                        gap: 5px;
                        line-height: 1.15;
                    }
                    .print-order-description-group b {
                        flex: 0 0 105px;
                        color: #25396f;
                    }
                    .print-order-description-group span {
                        flex: 1;
                    }
                    .print-order-signatures {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 18px;
                        margin-top: 28px;
                    }
                    .print-order-signatures div {
                        padding-top: 6px;
                        border-top: 1px solid #20222f;
                        text-align: center;
                    }
                    @media print {
                        @page { size: A4; margin: 10mm; }
                        body { padding: 0; }
                        .print-order { page-break-inside: avoid; }
                    }
                </style>
            `;

            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Orden de trabajo</title>' + style + '</head><body>');
            newWindow.document.write(template.innerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

    </script>
@endpush
