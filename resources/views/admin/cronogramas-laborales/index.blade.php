@extends('layouts.admin')

@php
    $canCreateCronogramas = auth()->user()?->can('cronogramas.crear');
    $canEditCronogramas = auth()->user()?->can('cronogramas.editar');
    $canDeleteCronogramas = auth()->user()?->can('cronogramas.eliminar');
    $isCompactByTipo = $tipo !== '';
@endphp

@push('styles')
<style>
    .cronograma-month-scroll {
        overflow-x: auto;
        overflow-y: visible;
        padding-bottom: .35rem;
    }

    .cronograma-table {
        width: max-content;
        min-width: 100%;
    }

    .cronograma-table th:first-child,
    .cronograma-table td:first-child {
        position: sticky;
        left: 0;
        z-index: 2;
        min-width: 210px;
        background-color: var(--bs-body-bg);
    }

    .cronograma-table thead th:first-child {
        z-index: 3;
    }

    .cronograma-turno-title {
        position: sticky;
        left: 0;
        background-color: var(--bs-body-bg);
        z-index: 1;
    }

    .cronograma-day-cell {
        min-width: 40px;
    }

    .cronograma-employee-cell {
        min-width: 210px;
    }

    .cronograma-month-block + .cronograma-month-block {
        margin-top: 1rem;
    }

    .cronograma-month-scroll .badge {
        min-width: 2rem;
    }

    .cronograma-row-meta {
        display: block;
        color: var(--bs-secondary-color);
        font-size: .8rem;
        line-height: 1.1;
    }

    @media (max-width: 768px) {
        .cronograma-table th:first-child,
        .cronograma-table td:first-child,
        .cronograma-employee-cell {
            min-width: 170px;
        }

        .cronograma-day-cell {
            min-width: 34px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-heading">
    <h3>Cronogramas laborales</h3>
    <p class="text-subtitle text-muted">Planificacion anual de trabajo y descansos por tipo de empleado.</p>
</div>

<div class="page-content">
    <section class="section">
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.cronogramas-laborales.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-2">
                        <label class="form-label">Anio</label>
                        <input type="number" min="2020" max="2100" name="year" value="{{ $year }}" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Mes</label>
                        <select name="mes" class="form-select">
                            <option value="0" @selected((int) $mes === 0)>Todos</option>
                            @foreach($meses as $mesNumero => $mesLabel)
                                <option value="{{ $mesNumero }}" @selected((int) $mes === (int) $mesNumero)>{{ $mesLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Tipo de empleado</label>
                        <select name="tipo_empleado" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposEmpleado as $tipoEmpleado)
                                <option value="{{ $tipoEmpleado }}" @selected($tipo === $tipoEmpleado)>{{ $tipoEmpleado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Turno</label>
                        <select name="turno" class="form-select">
                            <option value="">Todos</option>
                            @foreach($turnos as $turnoKey => $turnoLabel)
                                <option value="{{ $turnoKey }}" @selected($turno === $turnoKey)>{{ $turnoLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="{{ route('admin.cronogramas-laborales.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                        <a href="{{ route('admin.cronogramas-laborales.imprimir', ['year' => $year, 'mes' => $mes, 'tipo_empleado' => $tipo, 'turno' => $turno]) }}" target="_blank" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-printer"></i> Imprimir
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($canCreateCronogramas)
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header"><h4 class="card-title mb-0">Nuevo patron</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cronogramas-laborales.patrones.store') }}" class="row g-2">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="mes" value="{{ $mes }}">
                            <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
                            <input type="hidden" name="turno" value="{{ $turno }}">

                            <div class="col-12">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" maxlength="120" placeholder="Ej: 21x7" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Dias trabajo</label>
                                <input type="number" min="1" max="365" name="dias_trabajo" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Dias descanso</label>
                                <input type="number" min="1" max="365" name="dias_descanso" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Guardar patron</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header"><h4 class="card-title mb-0">Asignar patron a empleado</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cronogramas-laborales.asignaciones.store') }}" class="row g-2">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="mes" value="{{ $mes }}">
                            <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
                            <input type="hidden" name="turno" value="{{ $turno }}">

                            <div class="col-12">
                                <label class="form-label">Empleado</label>
                                <select name="empleado_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($empleados as $empleado)
                                        <option value="{{ $empleado->id }}">{{ $empleado->apellidos }}, {{ $empleado->nombres }} ({{ $empleado->tipo_empleado ?: 'Sin tipo' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Patron</label>
                                <select name="cronograma_patron_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($patrones as $patron)
                                        <option value="{{ $patron->id }}">{{ $patron->nombre }} ({{ $patron->dias_trabajo }}x{{ $patron->dias_descanso }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" name="fecha_fin" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Guardar asignacion</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header"><h4 class="card-title mb-0">Registrar novedad</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cronogramas-laborales.novedades.store') }}" class="row g-2">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="mes" value="{{ $mes }}">
                            <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
                            <input type="hidden" name="turno" value="{{ $turno }}">

                            <div class="col-12">
                                <label class="form-label">Empleado</label>
                                <select name="empleado_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($empleados as $empleado)
                                        <option value="{{ $empleado->id }}">{{ $empleado->apellidos }}, {{ $empleado->nombres }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tipo de novedad</label>
                                <select name="tipo" class="form-select" required>
                                    @foreach(\App\Models\CronogramaNovedad::TIPOS as $tipoNovedad => $tipoLabel)
                                        <option value="{{ $tipoNovedad }}">{{ $tipoLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripcion</label>
                                <input type="text" name="descripcion" class="form-control" maxlength="255" placeholder="Opcional">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Guardar novedad</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Patrones registrados</h4></div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Ciclo</th>
                                    <th>Estado</th>
                                    @if($canEditCronogramas || $canDeleteCronogramas)
                                        <th class="text-end">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patrones as $patron)
                                    <tr>
                                        <td>{{ $patron->nombre }}</td>
                                        <td>{{ $patron->dias_trabajo }}x{{ $patron->dias_descanso }}</td>
                                        <td>
                                            <span class="badge {{ $patron->estado === 'activo' ? 'bg-light-success' : 'bg-light-secondary' }}">{{ ucfirst($patron->estado) }}</span>
                                        </td>
                                        @if($canEditCronogramas || $canDeleteCronogramas)
                                            <td class="text-end">
                                                @if($canEditCronogramas)
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editPatronModal-{{ $patron->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @endif
                                                @if($canDeleteCronogramas)
                                                <form method="POST" action="{{ route('admin.cronogramas-laborales.patrones.destroy', $patron->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="year" value="{{ $year }}">
                                                    <input type="hidden" name="mes" value="{{ $mes }}">
                                                    <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
                                                    <input type="hidden" name="turno" value="{{ $turno }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirma eliminar este patron?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No hay patrones cargados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Ultimas asignaciones</h4></div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Patron</th>
                                    <th>Desde</th>
                                    <th>Hasta</th>
                                    @if($canEditCronogramas || $canDeleteCronogramas)
                                        <th class="text-end">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasAsignaciones as $asignacion)
                                    <tr>
                                        <td>{{ $asignacion->empleado?->apellidos }}, {{ $asignacion->empleado?->nombres }}</td>
                                        <td>{{ $asignacion->patron?->nombre }}</td>
                                        <td>{{ optional($asignacion->fecha_inicio)->format('d/m/Y') }}</td>
                                        <td>{{ optional($asignacion->fecha_fin)->format('d/m/Y') ?: '-' }}</td>
                                        @if($canEditCronogramas || $canDeleteCronogramas)
                                            <td class="text-end">
                                                @if($canEditCronogramas)
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editAsignacionModal-{{ $asignacion->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @endif
                                                @if($canDeleteCronogramas)
                                                <form method="POST" action="{{ route('admin.cronogramas-laborales.asignaciones.destroy', $asignacion->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="year" value="{{ $year }}">
                                                    <input type="hidden" name="mes" value="{{ $mes }}">
                                                    <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
                                                    <input type="hidden" name="turno" value="{{ $turno }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirma eliminar esta asignacion?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No hay asignaciones registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h4 class="card-title mb-0">Ultimas novedades registradas</h4></div>
            <div class="card-body table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Descripcion</th>
                            @if($canDeleteCronogramas)
                                <th class="text-end">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasNovedades as $novedad)
                            <tr>
                                <td>{{ optional($novedad->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $novedad->empleado?->apellidos }}, {{ $novedad->empleado?->nombres }}</td>
                                <td>{{ \App\Models\CronogramaNovedad::TIPOS[$novedad->tipo] ?? ucfirst($novedad->tipo) }}</td>
                                <td>{{ $novedad->descripcion ?: '-' }}</td>
                                @if($canDeleteCronogramas)
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.cronogramas-laborales.novedades.destroy', $novedad->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <input type="hidden" name="mes" value="{{ $mes }}">
                                            <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
                                            <input type="hidden" name="turno" value="{{ $turno }}">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirma eliminar esta novedad?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canDeleteCronogramas ? 5 : 4 }}" class="text-center text-muted">No hay novedades registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Cronograma anual {{ $year }}</h4>
                <div class="d-flex gap-3 small">
                    <span><span class="badge bg-light-success">M</span> Turno manana</span>
                    <span><span class="badge bg-light-success">T</span> Turno tarde</span>
                    <span><span class="badge bg-light-success">N</span> Turno noche</span>
                    <span><span class="badge bg-light-primary">M/T/N</span> Reemplazo franquero</span>
                    <span><span class="badge bg-light-info">M/T/N</span> Cobertura</span>
                    <span><span class="badge bg-light-secondary">FR</span> Franco</span>
                    <span><span class="badge bg-light-warning">FC</span> Franco comp.</span>
                    <span><span class="badge bg-light-info">L</span> Licencia</span>
                    <span><span class="badge bg-light-primary">FE</span> Feriado</span>
                    <span><span class="badge bg-light-dark">O</span> Otro</span>
                    <span><span class="badge bg-light-danger">-</span> Sin asignacion</span>
                </div>
            </div>
            <div class="card-body">
                @forelse($cronograma as $monthData)
                    @php
                        $turnosVisibles = $isCompactByTipo
                            ? collect($monthData['turnos'])->filter(fn ($turnoData) => !empty($turnoData['rows']))->values()
                            : collect($monthData['turnos']);
                        $allRows = $turnosVisibles->flatMap(fn ($turnoData) => $turnoData['rows'])->values();
                    @endphp

                    @if($allRows->isEmpty())
                        @continue
                    @endif

                    <div class="cronograma-month-block mb-2 {{ $isCompactByTipo ? 'pb-1 border-bottom' : '' }}">
                        <h5 class="{{ $isCompactByTipo ? 'mt-2 mb-2' : 'mt-3 mb-2' }}">{{ $monthData['month_name'] }}</h5>
                        <div class="cronograma-month-scroll">
                            <table class="cronograma-table table table-sm table-bordered mb-1 {{ $isCompactByTipo ? 'align-middle' : '' }}">
                                <thead>
                                    <tr>
                                        <th class="cronograma-employee-cell">Empleado</th>
                                        @for($d = 1; $d <= $monthData['days']; $d++)
                                            <th class="text-center cronograma-day-cell">{{ $d }}</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allRows as $row)
                                        <tr>
                                            <td class="cronograma-employee-cell">
                                                <div class="fw-semibold">{{ $row['empleado'] }}</div>
                                                @php
                                                    $turnoLabel = match($row['turno_key'] ?? 'sin_turno') {
                                                        'manana' => 'Turno manana',
                                                        'tarde' => 'Turno tarde',
                                                        'noche' => 'Turno noche',
                                                        default => 'Sin turno',
                                                    };
                                                @endphp
                                                @if($isCompactByTipo)
                                                    <small class="cronograma-row-meta">
                                                        @if($row['es_franquero']) Franquero @else Fijo @endif | {{ $turnoLabel }}
                                                    </small>
                                                @else
                                                    <small class="cronograma-row-meta">
                                                        {{ $row['tipo_empleado'] ?: 'Sin tipo' }}
                                                        | @if($row['es_franquero']) Franquero @else Fijo @endif
                                                        | {{ $turnoLabel }}
                                                    </small>
                                                @endif
                                            </td>
                                            @for($d = 1; $d <= $monthData['days']; $d++)
                                                @php $day = $row['days'][$d]; @endphp
                                                <td class="text-center cronograma-day-cell"><span class="badge {{ $day['class'] }}">{{ $day['code'] }}</span></td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No se pudo generar el cronograma.</p>
                @endforelse

                @if($isCompactByTipo && collect($cronograma)->every(fn ($monthData) => collect($monthData['turnos'])->every(fn ($turnoData) => empty($turnoData['rows']))))
                    <p class="text-muted mb-0">No hay empleados para el tipo seleccionado.</p>
                @endif
            </div>
        </div>
    </section>
</div>

@foreach($patrones as $patron)
<div class="modal fade" id="editPatronModal-{{ $patron->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.cronogramas-laborales.patrones.update', $patron->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="mes" value="{{ $mes }}">
            <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
            <input type="hidden" name="turno" value="{{ $turno }}">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" style="color:white">Editar patron</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-12">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" maxlength="120" value="{{ $patron->nombre }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Dias trabajo</label>
                    <input type="number" min="1" max="365" name="dias_trabajo" class="form-control" value="{{ $patron->dias_trabajo }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Dias descanso</label>
                    <input type="number" min="1" max="365" name="dias_descanso" class="form-control" value="{{ $patron->dias_descanso }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" @selected($patron->estado === 'activo')>Activo</option>
                        <option value="inactivo" @selected($patron->estado === 'inactivo')>Inactivo</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ $patron->observaciones }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@foreach($ultimasAsignaciones as $asignacion)
<div class="modal fade" id="editAsignacionModal-{{ $asignacion->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.cronogramas-laborales.asignaciones.update', $asignacion->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="mes" value="{{ $mes }}">
            <input type="hidden" name="tipo_empleado" value="{{ $tipo }}">
            <input type="hidden" name="turno" value="{{ $turno }}">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" style="color:white">Editar asignacion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-12">
                    <label class="form-label">Empleado</label>
                    <input type="text" class="form-control" value="{{ $asignacion->empleado?->apellidos }}, {{ $asignacion->empleado?->nombres }}" disabled>
                </div>
                <div class="col-12">
                    <label class="form-label">Patron</label>
                    <select name="cronograma_patron_id" class="form-select" required>
                        @foreach($patrones as $patron)
                            <option value="{{ $patron->id }}" @selected((int) $asignacion->cronograma_patron_id === (int) $patron->id)>
                                {{ $patron->nombre }} ({{ $patron->dias_trabajo }}x{{ $patron->dias_descanso }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ optional($asignacion->fecha_inicio)->format('Y-m-d') }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ optional($asignacion->fecha_fin)->format('Y-m-d') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" @selected($asignacion->estado === 'activo')>Activo</option>
                        <option value="inactivo" @selected($asignacion->estado === 'inactivo')>Inactivo</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ $asignacion->observaciones }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
