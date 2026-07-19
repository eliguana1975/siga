@extends('layouts.admin')

@php
    $estadoLabels = [
        'pendiente' => 'Pendiente',
        'enviada' => 'Enviada',
        'parcial' => 'Devuelta parcial',
        'completada' => 'Completada',
        'vencida' => 'Vencida',
        'cancelada' => 'Cancelada',
    ];

    $estadoBadges = [
        'pendiente' => 'bg-light-secondary',
        'enviada' => 'bg-light-primary',
        'parcial' => 'bg-light-info',
        'completada' => 'bg-light-success',
        'vencida' => 'bg-light-danger',
        'cancelada' => 'bg-light-dark',
    ];
    $reparacionesColspan = auth()->user()?->can('reparaciones-articulos.editar') ? 9 : 8;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Reparaciones de articulos</h3>
                <p class="text-subtitle text-muted">Control de articulos enviados a proveedor, pendientes y reclamos.</p>
            </div>
            @can('reparaciones-articulos.crear')
                <a href="{{ route('admin.reparaciones-articulos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva reparacion
                </a>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ordenes registradas</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reparaciones-articulos.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1" for="search">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control" value="{{ $search }}"
                                        placeholder="Nro orden, proveedor, articulo o estado">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="pendientes" name="pendientes" value="1" @checked($soloPendientes)>
                                    <label class="form-check-label" for="pendientes">Solo pendientes</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="vencidas" name="vencidas" value="1" @checked($soloVencidas)>
                                    <label class="form-check-label" for="vencidas">Solo vencidas</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.reparaciones-articulos.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @include('admin.partials.saved-filters', [
                        'filterKey' => 'reparaciones_articulos',
                        'filterRoute' => 'admin.reparaciones-articulos.index',
                    ])

                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.reparaciones-articulos.pendientes') }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-exclamation-circle"></i> Ver pendientes
                        </a>
                        <a href="{{ route('admin.reparaciones-articulos.index', ['vencidas' => 1]) }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-clock-history"></i> Ver vencidas
                        </a>
                    </div>

                    @can('reparaciones-articulos.editar')
                        <form method="POST" action="{{ route('admin.reparaciones-articulos.bulk') }}" id="reparacionesBulkForm" class="border rounded p-3 mb-3">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-lg-3">
                                    <label for="reparaciones_accion_masiva" class="form-label mb-1">Accion masiva</label>
                                    <select name="accion_masiva" id="reparaciones_accion_masiva" class="form-select">
                                        <option value="refrescar_estado">Refrescar estado</option>
                                        <option value="cancelar">Cancelar seleccionadas</option>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-7">
                                    <label for="reparaciones_observaciones_masivas" class="form-label mb-1">Observaciones</label>
                                    <input type="text" name="observaciones" id="reparaciones_observaciones_masivas" class="form-control"
                                        placeholder="Motivo o referencia interna">
                                </div>
                                <div class="col-12 col-lg-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check2-square"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">La accion se aplica solo a las reparaciones marcadas en esta pagina.</small>
                        </form>
                    @endcan

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    @can('reparaciones-articulos.editar')
                                        <th style="width: 42px;">
                                            <input type="checkbox" class="form-check-input" id="selectAllReparaciones" title="Seleccionar todas">
                                        </th>
                                    @endcan
                                    <th>Orden</th>
                                    <th>Proveedor</th>
                                    <th>Fecha envio</th>
                                    <th>Compromiso</th>
                                    <th>Items</th>
                                    <th>Pendientes</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reparaciones as $reparacion)
                                    @php
                                        $cantidadItems = $reparacion->detalles->count();
                                        $pendientes = (int) $reparacion->detalles->sum(fn ($detalle) => $detalle->cantidadPendiente());
                                    @endphp
                                    <tr>
                                        @can('reparaciones-articulos.editar')
                                            <td>
                                                <input type="checkbox" class="form-check-input reparacion-bulk-checkbox" form="reparacionesBulkForm" name="ids[]" value="{{ $reparacion->id }}">
                                            </td>
                                        @endcan
                                        <td>
                                            <strong>{{ $reparacion->numero_orden }}</strong>
                                            <small class="d-block text-muted">#{{ $reparacion->id }}</small>
                                        </td>
                                        <td>{{ $reparacion->proveedor?->nombre ?? '-' }}</td>
                                        <td>{{ $reparacion->fecha_envio?->format('d/m/Y') }}</td>
                                        <td>
                                            {{ $reparacion->fecha_compromiso?->format('d/m/Y') ?? '-' }}
                                            @if ($reparacion->fecha_compromiso && $reparacion->fecha_compromiso->isPast() && $pendientes > 0)
                                                <small class="d-block text-danger">Atrasada</small>
                                            @endif
                                        </td>
                                        <td>{{ $cantidadItems }}</td>
                                        <td>
                                            <span class="fw-bold {{ $pendientes > 0 ? 'text-danger' : 'text-success' }}">{{ $pendientes }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$reparacion->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$reparacion->estado] ?? ucfirst((string) $reparacion->estado) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.reparaciones-articulos.show', $reparacion->id) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('reparaciones-articulos.editar')
                                                <a href="{{ route('admin.reparaciones-articulos.edit', $reparacion->id) }}" class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.reparaciones-articulos.destroy', $reparacion->id) }}" class="d-inline" onsubmit="return confirm('¿Seguro que desea eliminar esta orden de reparacion?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('reparaciones-articulos.imprimir')
                                                <button type="button" class="btn btn-sm btn-secondary" title="Imprimir planilla"
                                                    onclick="printReparacionPlanilla('printReparacionPlanilla-{{ $reparacion->id }}')">
                                                    <i class="bi bi-printer"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" title="Imprimir rotulos"
                                                    onclick="printReparacionRotulos('printReparacionRotulos-{{ $reparacion->id }}')">
                                                    <i class="bi bi-tag"></i>
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $reparacionesColspan }}" class="text-center text-muted py-4">No hay ordenes de reparacion registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($reparaciones->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $reparaciones->firstItem() }} a {{ $reparaciones->lastItem() }} de {{ $reparaciones->total() }} registros
                            </small>
                            <div>{{ $reparaciones->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($reparaciones as $reparacion)
        @include('admin.reparaciones-articulos.partials.print-planilla', ['reparacion' => $reparacion])
        @include('admin.reparaciones-articulos.partials.print-rotulos', ['reparacion' => $reparacion])
    @endforeach
@endsection

@include('admin.reparaciones-articulos.partials.print-script')

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAllReparaciones');
            const checkboxes = Array.from(document.querySelectorAll('.reparacion-bulk-checkbox'));
            const form = document.getElementById('reparacionesBulkForm');

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }

            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!checkboxes.some(function(checkbox) { return checkbox.checked; })) {
                        event.preventDefault();
                        window.alert('Seleccione al menos una reparacion.');
                    }
                });
            }
        });
    </script>
@endpush
