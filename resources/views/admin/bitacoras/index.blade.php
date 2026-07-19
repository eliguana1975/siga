@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div>
            <h3>Bitacora del sistema</h3>
            <p class="text-subtitle text-muted">
                Registro de acciones realizadas por usuarios y procesos internos.
            </p>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="card mb-0">
                        <div class="card-body">
                            <small class="text-muted d-block">Registros filtrados</small>
                            <h3 class="mb-0">{{ number_format($totales['registros'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card mb-0">
                        <div class="card-body">
                            <small class="text-muted d-block">Usuarios involucrados</small>
                            <h3 class="mb-0">{{ number_format($totales['usuarios'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card mb-0">
                        <div class="card-body">
                            <small class="text-muted d-block">Modulos afectados</small>
                            <h3 class="mb-0">{{ number_format($totales['modulos'], 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-4">
                    <div class="card mb-0 h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Por accion</h4></div>
                        <div class="card-body">
                            @forelse ($porAccion as $nombre => $total)
                                <div class="d-flex justify-content-between border-bottom py-1">
                                    <span>{{ ucfirst($nombre) }}</span>
                                    <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                                </div>
                            @empty
                                <span class="text-muted">Sin datos.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card mb-0 h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Por modulo</h4></div>
                        <div class="card-body">
                            @forelse ($porModulo as $nombre => $total)
                                <div class="d-flex justify-content-between border-bottom py-1">
                                    <span>{{ $nombre }}</span>
                                    <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                                </div>
                            @empty
                                <span class="text-muted">Sin datos.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card mb-0 h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Por usuario</h4></div>
                        <div class="card-body">
                            @forelse ($porUsuario as $nombre => $total)
                                <div class="d-flex justify-content-between border-bottom py-1">
                                    <span>{{ $nombre }}</span>
                                    <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                                </div>
                            @empty
                                <span class="text-muted">Sin datos.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Historial de acciones</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bitacoras.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-lg-4">
                                <label for="search" class="form-label mb-1">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search }}" placeholder="Usuario, modulo, descripcion o entidad">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="accion" class="form-label mb-1">Accion</label>
                                <select name="accion" id="accion" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach ($acciones as $accionOption)
                                        <option value="{{ $accionOption }}" @selected($accion === $accionOption)>
                                            {{ ucfirst($accionOption) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="modulo" class="form-label mb-1">Modulo</label>
                                <select name="modulo" id="modulo" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach ($modulos as $moduloOption)
                                        <option value="{{ $moduloOption }}" @selected($modulo === $moduloOption)>
                                            {{ $moduloOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="user_id" class="form-label mb-1">Usuario</label>
                                <select name="user_id" id="user_id" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" @selected((string) $usuarioId === (string) $usuario->id)>
                                            {{ $usuario->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-lg-2">
                                <label for="fecha_desde" class="form-label mb-1">Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                                    value="{{ $fechaDesde }}">
                            </div>
                            <div class="col-6 col-lg-2">
                                <label for="fecha_hasta" class="form-label mb-1">Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                                    value="{{ $fechaHasta }}">
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.bitacoras.export', request()->query()) }}" class="btn btn-success">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
                                </a>
                                <a href="{{ route('admin.bitacoras.index') }}" class="btn btn-light-secondary">
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </form>

                    @include('admin.partials.saved-filters', [
                        'filterKey' => 'bitacoras',
                        'filterRoute' => 'admin.bitacoras.index',
                    ])

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Fecha</th>
                                    <th style="width: 180px;">Usuario</th>
                                    <th style="width: 120px;">Accion</th>
                                    <th style="width: 160px;">Modulo</th>
                                    <th>Descripcion</th>
                                    <th style="width: 110px;">Entidad</th>
                                    <th class="text-end" style="width: 90px;">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bitacoras as $bitacora)
                                    <tr>
                                        <td>{{ $bitacora->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>{{ $bitacora->usuario?->name ?? $bitacora->usuario_nombre ?? 'Sistema' }}</td>
                                        <td>
                                            <span class="badge bg-light-primary">{{ ucfirst($bitacora->accion) }}</span>
                                        </td>
                                        <td>{{ $bitacora->modulo ?? '-' }}</td>
                                        <td>{{ $bitacora->descripcion }}</td>
                                        <td>
                                            @if ($bitacora->entidad_type)
                                                {{ class_basename($bitacora->entidad_type) }} #{{ $bitacora->entidad_id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#bitacoraModal-{{ $bitacora->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No hay acciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($bitacoras->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $bitacoras->firstItem() }} a {{ $bitacoras->lastItem() }} de {{ $bitacoras->total() }}
                                registros
                            </small>
                            <div>
                                {{ $bitacoras->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($bitacoras as $bitacora)
        <div class="modal fade" id="bitacoraModal-{{ $bitacora->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" style="color: white">Detalle de bitacora #{{ $bitacora->id }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row mb-0">
                            <dt class="col-md-3">Fecha</dt>
                            <dd class="col-md-9">{{ $bitacora->created_at?->format('d/m/Y H:i:s') }}</dd>

                            <dt class="col-md-3">Usuario</dt>
                            <dd class="col-md-9">{{ $bitacora->usuario?->name ?? $bitacora->usuario_nombre ?? 'Sistema' }}</dd>

                            <dt class="col-md-3">Descripcion</dt>
                            <dd class="col-md-9">{{ $bitacora->descripcion }}</dd>

                            <dt class="col-md-3">Ruta</dt>
                            <dd class="col-md-9">{{ $bitacora->method }} {{ $bitacora->route_name ?? $bitacora->url ?? '-' }}</dd>

                            <dt class="col-md-3">IP</dt>
                            <dd class="col-md-9">{{ $bitacora->ip_address ?? '-' }}</dd>
                        </dl>

                        <div class="row g-3 mt-1">
                            <div class="col-12 col-lg-6">
                                <h6>Datos anteriores</h6>
                                <pre class="bg-light p-3 rounded small mb-0">{{ json_encode($bitacora->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div class="col-12 col-lg-6">
                                <h6>Datos nuevos</h6>
                                <pre class="bg-light p-3 rounded small mb-0">{{ json_encode($bitacora->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div class="col-12">
                                <h6>Metadata</h6>
                                <pre class="bg-light p-3 rounded small mb-0">{{ json_encode($bitacora->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
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
