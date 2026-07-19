@extends('layouts.admin')

@php
    $licenseStatus = function ($date, string $estado): ?string {
        if ($estado !== 'activo' || ! $date) {
            return null;
        }

        $daysUntilExpiration = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

        if ($daysUntilExpiration < 0) {
            return 'expired';
        }

        return $daysUntilExpiration <= 30 ? 'soon' : null;
    };

    $licenseClass = [
        'expired' => 'table-danger text-dark',
        'soon' => 'table-warning text-dark',
    ];

    $licenseBadge = [
        'expired' => ['class' => 'bg-danger text-white', 'text' => 'Vencido'],
        'soon' => ['class' => 'bg-warning text-dark', 'text' => 'Por vencer'],
    ];

    $canCreateEmpleados = auth()->user()?->can('empleados.crear');
    $canEditEmpleados = auth()->user()?->can('empleados.editar');
    $canDeleteEmpleados = auth()->user()?->can('empleados.eliminar');
    $showEmpleadoActions = $canEditEmpleados || $canDeleteEmpleados;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Empleados</h3>
                <p class="text-subtitle text-muted">Administra los empleados, su documentacion, base y estado.</p>
            </div>
            @if ($canCreateEmpleados)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEmpleadoModal">
                <i class="bi bi-plus-circle"></i> Nuevo empleado
            </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de empleados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.empleados.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="search" class="form-label mb-1">Buscar empleado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre, tipo, documento, telefono, carnet, base...">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.empleados.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'empleados_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'empleados_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'empleados_registrados')">
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
                            Se encontraron {{ $empleados->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Empleado</th>
                                    <th>Tipo</th>
                                    <th>Turno</th>
                                    <th>Cobertura</th>
                                    <th>Documento</th>
                                    <th>Telefono</th>
                                    <th>Carnet</th>
                                    <th>LINTI</th>
                                    <th>Usuario</th>
                                    <th>Base</th>
                                    <th>Estado</th>
                                    @if ($showEmpleadoActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($empleados as $empleado)
                                    @php
                                        $carnetStatus = $licenseStatus($empleado->vencimiento_carnet_conducir, $empleado->estado);
                                        $lintiStatus = $licenseStatus($empleado->vencimiento_linti, $empleado->estado);
                                    @endphp
                                    <tr>
                                        <td>{{ $empleados->firstItem() + $loop->index }}</td>
                                        <td>{{ $empleado->apellidos }}, {{ $empleado->nombres }}</td>
                                        <td>{{ $empleado->tipo_empleado ?: 'Sin tipo' }}</td>
                                        <td>{{ $empleado->turno_laboral ? ucfirst($empleado->turno_laboral) : 'Sin turno' }}</td>
                                        <td>
                                            @if($empleado->es_franquero)
                                                <span class="badge bg-light-primary">Franquero</span>
                                                @if($empleado->franquero_de_empleado_id)
                                                    <div><small class="text-muted">Reemplazo directo</small></div>
                                                @elseif($empleado->franquero_de_tipo_empleado)
                                                    <div><small class="text-muted">De: {{ $empleado->franquero_de_tipo_empleado }}</small></div>
                                                @endif
                                            @else
                                                <span class="badge bg-light-secondary">Fijo</span>
                                            @endif
                                        </td>
                                        <td>{{ $empleado->tipo_doc }} {{ $empleado->numero_doc }}</td>
                                        <td>{{ $empleado->telefono }}</td>
                                        <td class="{{ $licenseClass[$carnetStatus] ?? '' }}">
                                            <div class="fw-semibold text-dark">{{ $empleado->categoria_carnet_conducir ?: 'N/A' }}</div>
                                            <small class="text-dark">
                                                Vto: {{ optional($empleado->vencimiento_carnet_conducir)->format('d/m/Y') ?? 'N/A' }}
                                            </small>
                                            @if ($carnetStatus)
                                                <div class="mt-1">
                                                    <span class="badge {{ $licenseBadge[$carnetStatus]['class'] }}">
                                                        Carnet {{ $licenseBadge[$carnetStatus]['text'] }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="{{ $licenseClass[$lintiStatus] ?? '' }}">
                                            {{ optional($empleado->vencimiento_linti)->format('d/m/Y') ?? 'No aplica' }}
                                            @if ($lintiStatus)
                                                <div class="mt-1">
                                                    <span class="badge {{ $licenseBadge[$lintiStatus]['class'] }}">
                                                        LINTI {{ $licenseBadge[$lintiStatus]['text'] }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $empleado->usuario?->name }}</td>
                                        <td>{{ $empleado->base?->nombre }}</td>
                                        <td>
                                            @if ($empleado->estado === 'activo')
                                                <span class="badge bg-light-success">Activo</span>
                                            @else
                                                <span class="badge bg-light-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        @if ($showEmpleadoActions)
                                        <td class="text-end">
                                            @if ($canEditEmpleados)
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editEmpleadoModal-{{ $empleado->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @endif
                                            @if ($canDeleteEmpleados)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteEmpleadoModal-{{ $empleado->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showEmpleadoActions ? 13 : 12 }}" class="text-center text-muted py-4">
                                            No hay empleados registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($empleados->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $empleados->firstItem() }} a {{ $empleados->lastItem() }} de {{ $empleados->total() }} registros
                            </small>
                            <div>
                                {{ $empleados->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="createEmpleadoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content empleado-modal" method="POST" action="{{ route('admin.empleados.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Nuevo empleado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'createEmpleadoModal')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres (*)</label>
                            <input type="text" name="nombres" class="form-control" value="{{ old('nombres') }}" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos (*)</label>
                            <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos') }}" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo empleado</label>
                            <input type="text" name="tipo_empleado" class="form-control" value="{{ old('tipo_empleado') }}" maxlength="100"
                                placeholder="Ej: MECANICO, SUPERVISOR, PANOLERO, CHOFER">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Turno</label>
                            <select name="turno_laboral" class="form-select">
                                <option value="">Sin turno</option>
                                <option value="manana" @selected(old('turno_laboral') === 'manana')>Manana</option>
                                <option value="tarde" @selected(old('turno_laboral') === 'tarde')>Tarde</option>
                                <option value="noche" @selected(old('turno_laboral') === 'noche')>Noche</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="hidden" name="es_franquero" value="0">
                                <input class="form-check-input" type="checkbox" name="es_franquero" value="1" id="es_franquero_create" @checked(old('es_franquero'))>
                                <label class="form-check-label" for="es_franquero_create">Es franquero</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Franquero de tipo</label>
                            <input type="text" name="franquero_de_tipo_empleado" class="form-control" value="{{ old('franquero_de_tipo_empleado') }}" maxlength="100"
                                placeholder="Ej: PANOLERO, SUPERVISOR DE TALLER">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Franquero de empleado</label>
                            <select name="franquero_de_empleado_id" class="form-select">
                                <option value="">Sin reemplazo directo</option>
                                @foreach($empleadosActivos as $empleadoActivo)
                                    <option value="{{ $empleadoActivo->id }}" @selected((string) old('franquero_de_empleado_id') === (string) $empleadoActivo->id)>
                                        {{ $empleadoActivo->apellidos }}, {{ $empleadoActivo->nombres }} ({{ $empleadoActivo->tipo_empleado ?: 'Sin tipo' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo doc. (*)</label>
                            <select name="tipo_doc" class="form-select" required>
                                <option value="DNI" @selected(old('tipo_doc', 'DNI') === 'DNI')>DNI</option>
                                <option value="CI" @selected(old('tipo_doc') === 'CI')>CI</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Numero doc. (*)</label>
                            <input type="text" name="numero_doc" class="form-control" value="{{ old('numero_doc') }}" maxlength="50" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" maxlength="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria carnet (*)</label>
                            <input type="text" name="categoria_carnet_conducir" class="form-control"
                                value="{{ old('categoria_carnet_conducir') }}" maxlength="50" required
                                placeholder="Ej: B1, C, D">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vto. carnet (*)</label>
                            <input type="date" name="vencimiento_carnet_conducir" class="form-control"
                                value="{{ old('vencimiento_carnet_conducir') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vto. LINTI</label>
                            <input type="date" name="vencimiento_linti" class="form-control"
                                value="{{ old('vencimiento_linti') }}">
                            <small class="text-muted">Solo choferes.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuario</label>
                            <select name="usuario_id" class="form-select">
                                <option value="">Seleccione usuario</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" @selected((string) old('usuario_id') === (string) $usuario->id)>
                                        {{ $usuario->name }} - {{ $usuario->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Base</label>
                            <select name="base_id" class="form-select">
                                <option value="">Seleccione base</option>
                                @foreach ($bases as $base)
                                    <option value="{{ $base->id }}" @selected((string) old('base_id') === (string) $base->id)>
                                        {{ $base->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado (*)</label>
                            <select name="estado" class="form-select" required>
                                <option value="activo" @selected(old('estado', 'activo') === 'activo')>Activo</option>
                                <option value="inactivo" @selected(old('estado') === 'inactivo')>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Direccion</label>
                            <textarea name="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($empleados as $empleado)
        <div class="modal fade" id="editEmpleadoModal-{{ $empleado->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content empleado-modal" method="POST" action="{{ route('admin.empleados.update', $empleado->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar empleado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any() && session('open_modal') === 'editEmpleadoModal-' . $empleado->id)
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $selectedUsuarioId = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('usuario_id', $empleado->usuario_id) : $empleado->usuario_id;
                            $selectedBaseId = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('base_id', $empleado->base_id) : $empleado->base_id;
                            $selectedTipoEmpleado = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('tipo_empleado', $empleado->tipo_empleado) : $empleado->tipo_empleado;
                            $selectedTurnoLaboral = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('turno_laboral', $empleado->turno_laboral) : $empleado->turno_laboral;
                            $selectedEsFranquero = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? (bool) old('es_franquero', $empleado->es_franquero) : (bool) $empleado->es_franquero;
                            $selectedFranqueroTipo = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('franquero_de_tipo_empleado', $empleado->franquero_de_tipo_empleado) : $empleado->franquero_de_tipo_empleado;
                            $selectedFranqueroEmpleadoId = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('franquero_de_empleado_id', $empleado->franquero_de_empleado_id) : $empleado->franquero_de_empleado_id;
                            $selectedTipoDoc = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('tipo_doc', $empleado->tipo_doc) : $empleado->tipo_doc;
                            $selectedEstado = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('estado', $empleado->estado) : $empleado->estado;
                            $selectedCategoriaCarnet = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('categoria_carnet_conducir', $empleado->categoria_carnet_conducir) : $empleado->categoria_carnet_conducir;
                            $selectedVencimientoCarnet = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('vencimiento_carnet_conducir', optional($empleado->vencimiento_carnet_conducir)->format('Y-m-d')) : optional($empleado->vencimiento_carnet_conducir)->format('Y-m-d');
                            $selectedVencimientoLinti = session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('vencimiento_linti', optional($empleado->vencimiento_linti)->format('Y-m-d')) : optional($empleado->vencimiento_linti)->format('Y-m-d');
                        @endphp

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres (*)</label>
                                <input type="text" name="nombres" class="form-control"
                                    value="{{ session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('nombres', $empleado->nombres) : $empleado->nombres }}"
                                    maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos (*)</label>
                                <input type="text" name="apellidos" class="form-control"
                                    value="{{ session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('apellidos', $empleado->apellidos) : $empleado->apellidos }}"
                                    maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo empleado</label>
                                <input type="text" name="tipo_empleado" class="form-control"
                                    value="{{ $selectedTipoEmpleado }}"
                                    maxlength="100" placeholder="Ej: MECANICO, SUPERVISOR, PANOLERO, CHOFER">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Turno</label>
                                <select name="turno_laboral" class="form-select">
                                    <option value="">Sin turno</option>
                                    <option value="manana" @selected($selectedTurnoLaboral === 'manana')>Manana</option>
                                    <option value="tarde" @selected($selectedTurnoLaboral === 'tarde')>Tarde</option>
                                    <option value="noche" @selected($selectedTurnoLaboral === 'noche')>Noche</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input type="hidden" name="es_franquero" value="0">
                                    <input class="form-check-input" type="checkbox" name="es_franquero" value="1" id="es_franquero_edit_{{ $empleado->id }}" @checked($selectedEsFranquero)>
                                    <label class="form-check-label" for="es_franquero_edit_{{ $empleado->id }}">Es franquero</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Franquero de tipo</label>
                                <input type="text" name="franquero_de_tipo_empleado" class="form-control"
                                    value="{{ $selectedFranqueroTipo }}" maxlength="100"
                                    placeholder="Ej: PANOLERO, SUPERVISOR DE TALLER">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Franquero de empleado</label>
                                <select name="franquero_de_empleado_id" class="form-select">
                                    <option value="">Sin reemplazo directo</option>
                                    @foreach($empleadosActivos as $empleadoActivo)
                                        <option value="{{ $empleadoActivo->id }}" @selected((string) $selectedFranqueroEmpleadoId === (string) $empleadoActivo->id)>
                                            {{ $empleadoActivo->apellidos }}, {{ $empleadoActivo->nombres }} ({{ $empleadoActivo->tipo_empleado ?: 'Sin tipo' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo doc. (*)</label>
                                <select name="tipo_doc" class="form-select" required>
                                    <option value="DNI" @selected($selectedTipoDoc === 'DNI')>DNI</option>
                                    <option value="CI" @selected($selectedTipoDoc === 'CI')>CI</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Numero doc. (*)</label>
                                <input type="text" name="numero_doc" class="form-control"
                                    value="{{ session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('numero_doc', $empleado->numero_doc) : $empleado->numero_doc }}"
                                    maxlength="50" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" name="telefono" class="form-control"
                                    value="{{ session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('telefono', $empleado->telefono) : $empleado->telefono }}"
                                    maxlength="50">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control"
                                    value="{{ session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('fecha_nacimiento', optional($empleado->fecha_nacimiento)->format('Y-m-d')) : optional($empleado->fecha_nacimiento)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Categoria carnet (*)</label>
                                <input type="text" name="categoria_carnet_conducir" class="form-control"
                                    value="{{ $selectedCategoriaCarnet }}" maxlength="50" required
                                    placeholder="Ej: B1, C, D">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Vto. carnet (*)</label>
                                <input type="date" name="vencimiento_carnet_conducir" class="form-control"
                                    value="{{ $selectedVencimientoCarnet }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Vto. LINTI</label>
                                <input type="date" name="vencimiento_linti" class="form-control"
                                    value="{{ $selectedVencimientoLinti }}">
                                <small class="text-muted">Solo choferes.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Usuario</label>
                                <select name="usuario_id" class="form-select">
                                    <option value="">Seleccione usuario</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" @selected((string) $selectedUsuarioId === (string) $usuario->id)>
                                            {{ $usuario->name }} - {{ $usuario->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Base</label>
                                <select name="base_id" class="form-select">
                                    <option value="">Seleccione base</option>
                                    @foreach ($bases as $base)
                                        <option value="{{ $base->id }}" @selected((string) $selectedBaseId === (string) $base->id)>
                                            {{ $base->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado (*)</label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" @selected($selectedEstado === 'activo')>Activo</option>
                                    <option value="inactivo" @selected($selectedEstado === 'inactivo')>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Direccion</label>
                                <textarea name="direccion" class="form-control" rows="2">{{ session('open_modal') === 'editEmpleadoModal-' . $empleado->id ? old('direccion', $empleado->direccion) : $empleado->direccion }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="deleteEmpleadoModal-{{ $empleado->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.empleados.destroy', $empleado->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar empleado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">
                            ¿Está seguro de eliminar a <strong>{{ $empleado->apellidos }}, {{ $empleado->nombres }}</strong>?
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
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
    </script>
@endpush
