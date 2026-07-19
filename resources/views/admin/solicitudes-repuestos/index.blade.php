@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Solicitudes de repuestos</h3>
                <p class="text-subtitle text-muted">Repuestos no catalogados solicitados por taller y procesados por compras.</p>
            </div>
            @can('solicitudes-repuestos.crear')
                <a href="{{ route('admin.solicitudes-repuestos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva solicitud
                </a>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $type)
                @if (session($key))
                    <div class="alert alert-{{ $type }} alert-dismissible show fade">
                        {{ session($key) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @endforeach

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de solicitudes</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.solicitudes-repuestos.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-7">
                                <label for="search" class="form-label mb-1">Buscar solicitud</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Repuesto, codigo, interno, dominio, chasis o solicitante">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="estado" class="form-label mb-1">Estado</label>
                                <select name="estado" id="estado" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach (\App\Models\SolicitudRepuesto::ESTADOS as $value => $label)
                                        <option value="{{ $value }}" @selected(($estado ?? request('estado')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.solicitudes-repuestos.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @include('admin.partials.saved-filters', [
                        'filterKey' => 'solicitudes_repuestos',
                        'filterRoute' => 'admin.solicitudes-repuestos.index',
                    ])

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'solicitudes_repuestos')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'solicitudes_repuestos')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'solicitudes_repuestos')">
                                <i class="bi bi-file-earmark-pdf"></i> Crear PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="printTable('datatable')">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.solicitudes-repuestos.bulk') }}" id="solicitudesBulkForm">
                        @csrf
                        @canany(['solicitudes-repuestos.aprobar', 'solicitudes-repuestos.rechazar', 'solicitudes-repuestos.cerrar'])
                            <div class="border rounded p-3 mb-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-lg-3">
                                        <label for="accion_masiva" class="form-label mb-1">Accion masiva</label>
                                        <select name="accion_masiva" id="accion_masiva" class="form-select">
                                            @can('solicitudes-repuestos.aprobar')
                                                <option value="aprobar">Aprobar seleccionadas</option>
                                            @endcan
                                            @can('solicitudes-repuestos.rechazar')
                                                <option value="rechazar">Rechazar seleccionadas</option>
                                            @endcan
                                            @can('solicitudes-repuestos.cerrar')
                                                <option value="estado">Cambiar estado</option>
                                            @endcan
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label for="estado_masivo" class="form-label mb-1">Estado destino</label>
                                        <select name="estado" id="estado_masivo" class="form-select">
                                            <option value="">Seleccione</option>
                                            <option value="comprado">Comprado</option>
                                            <option value="ingresado">Ingresado</option>
                                            <option value="entregado_taller">Entregado a taller</option>
                                            <option value="colocado">Colocado</option>
                                            <option value="cerrado">Cerrado</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label for="observaciones_masivas" class="form-label mb-1">Observaciones</label>
                                        <input type="text" name="observaciones_compras" id="observaciones_masivas" class="form-control"
                                            placeholder="Motivo, nota o referencia interna">
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-check2-square"></i> Aplicar
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">La accion se aplica solo a las solicitudes marcadas en esta pagina.</small>
                            </div>
                        @endcanany

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 42px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllSolicitudes" title="Seleccionar todas">
                                    </th>
                                    <th style="width: 70px;">#</th>
                                    <th>Fecha</th>
                                    <th>Repuesto</th>
                                    <th>Vehiculo</th>
                                    <th>Cant.</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Pedido</th>
                                    <th class="text-end" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($solicitudes as $solicitud)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input solicitud-bulk-checkbox" name="ids[]" value="{{ $solicitud->id }}">
                                        </td>
                                        <td>{{ $solicitudes->firstItem() + $loop->index }}</td>
                                        <td>{{ $solicitud->fecha_solicitud?->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $solicitud->descripcion_repuesto }}</div>
                                            <small class="text-muted">{{ $solicitud->codigo_repuesto ?: 'Sin codigo' }}</small>
                                        </td>
                                        <td>
                                            @if ($solicitud->flota)
                                                <div>{{ $solicitud->flota->nro_interno }} - {{ $solicitud->flota->dominio }}</div>
                                                <small class="text-muted">{{ $solicitud->flota->nro_chasis }}</small>
                                            @else
                                                <span class="text-muted">{{ $solicitud->nro_chasis ?: '-' }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $solicitud->cantidad }}</td>
                                        <td><span class="badge {{ $solicitud->prioridadBadge() }}">{{ $solicitud->prioridadLabel() }}</span></td>
                                        <td><span class="badge {{ $solicitud->estadoBadge() }}">{{ $solicitud->estadoLabel() }}</span></td>
                                        <td>{{ $solicitud->pedido_articulo_id ? 'Pedido #' . $solicitud->pedido_articulo_id : '-' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.solicitudes-repuestos.show', $solicitud) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('solicitudes-repuestos.editar')
                                                <a href="{{ route('admin.solicitudes-repuestos.edit', $solicitud) }}" class="btn btn-sm btn-success" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No hay solicitudes registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </form>

                    @if ($solicitudes->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $solicitudes->firstItem() }} a {{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }} registros
                            </small>
                            <div>{{ $solicitudes->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAllSolicitudes');
            const checkboxes = Array.from(document.querySelectorAll('.solicitud-bulk-checkbox'));
            const form = document.getElementById('solicitudesBulkForm');
            const actionSelect = document.getElementById('accion_masiva');
            const estadoSelect = document.getElementById('estado_masivo');

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }

            function syncEstado() {
                if (!actionSelect || !estadoSelect) {
                    return;
                }

                estadoSelect.disabled = actionSelect.value !== 'estado';
                if (estadoSelect.disabled) {
                    estadoSelect.value = '';
                }
            }

            if (actionSelect) {
                actionSelect.addEventListener('change', syncEstado);
                syncEstado();
            }

            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!checkboxes.some(function(checkbox) { return checkbox.checked; })) {
                        event.preventDefault();
                        window.alert('Seleccione al menos una solicitud.');
                    }
                });
            }
        });
    </script>
@endpush
