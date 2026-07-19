@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Check List Vehicular</h3>
                <p class="text-subtitle text-muted">Registra y consulta controles operativos de unidades de flota.</p>
            </div>
            @can('controles-unidad.crear')
                <a href="{{ route('admin.controles-unidad.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo checklist
                </a>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de checklists</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.controles-unidad.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar checklist</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Interno, conductor, servicio u observaciones...">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.controles-unidad.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    <th>Fecha</th>
                                    <th>Interno</th>
                                    <th>Conductor</th>
                                    <th>Kilometraje</th>
                                    <th>Servicio</th>
                                    <th>Orden</th>
                                    <th>Usuario</th>
                                    <th class="text-end" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($controles as $control)
                                    <tr>
                                        <td>{{ $controles->firstItem() + $loop->index }}</td>
                                        <td>{{ optional($control->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ $control->flota?->nro_interno ?? $control->interno }}</td>
                                        <td>{{ $control->conductorUser?->name ?? $control->conductor }}</td>
                                        <td>{{ number_format($control->kilometraje_actual, 0, ',', '.') }} km</td>
                                        <td>{{ $control->servicioAsignado?->nombre ?? $control->servicio_asignado }}</td>
                                        <td>
                                            @if ($control->ordenTrabajo)
                                                <span class="badge bg-light-warning">Pendiente</span>
                                            @else
                                                <span class="text-muted">Sin orden</span>
                                            @endif
                                        </td>
                                        <td>{{ $control->user?->name ?? 'Sistema' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.controles-unidad.show', $control) }}" class="btn btn-sm btn-info" title="Ver control">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('controles-unidad.eliminar')
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteControlUnidadModal-{{ $control->id }}" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No hay checklists vehiculares registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($controles->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $controles->firstItem() }} a {{ $controles->lastItem() }} de {{ $controles->total() }} registros
                            </small>
                            <div>
                                {{ $controles->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @can('controles-unidad.eliminar')
    @foreach ($controles as $control)
        <div class="modal fade" id="deleteControlUnidadModal-{{ $control->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.controles-unidad.destroy', $control) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar control</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Se eliminara el checklist del interno <strong>{{ $control->interno }}</strong> realizado a
                        <strong>{{ $control->conductor }}</strong>.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
    @endcan
@endsection
