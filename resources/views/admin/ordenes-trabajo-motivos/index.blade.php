@extends('layouts.admin')

@section('content')
<div class="page-heading">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>
            <h3>Motivos de ordenes de trabajo</h3>
            <p class="text-subtitle text-muted">Administra los motivos disponibles al crear una orden de trabajo.</p>
        </div>
        @can('ordenes-trabajo-motivos.crear')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMotivoModal">
                <i class="bi bi-plus-circle"></i> Nuevo motivo
            </button>
        @endcan
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Listado de motivos</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.ordenes-trabajo-motivos.index') }}" class="mb-3">
                    <label class="form-label">Buscar motivo</label>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nombre o codigo">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.ordenes-trabajo-motivos.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>Nombre</th>
                                <th>Codigo</th>
                                <th>Estado</th>
                                <th class="text-end" style="width: 180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($motivos as $motivo)
                                <tr>
                                    <td>{{ $motivos->firstItem() + $loop->index }}</td>
                                    <td>{{ $motivo->nombre }}</td>
                                    <td><code>{{ $motivo->codigo }}</code></td>
                                    <td>
                                        <span class="badge {{ $motivo->activo ? 'bg-light-success' : 'bg-light-secondary' }}">
                                            {{ $motivo->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @can('ordenes-trabajo-motivos.editar')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editMotivoModal-{{ $motivo->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        @endcan
                                        @can('ordenes-trabajo-motivos.eliminar')
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteMotivoModal-{{ $motivo->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay motivos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($motivos->count() > 0)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                        <small class="text-muted">
                            Mostrando {{ $motivos->firstItem() }} a {{ $motivos->lastItem() }} de {{ $motivos->total() }} registros
                        </small>
                        <div>{{ $motivos->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

@can('ordenes-trabajo-motivos.crear')
<div class="modal fade" id="createMotivoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('admin.ordenes-trabajo-motivos.store') }}">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo motivo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any() && session('open_modal') === 'createMotivoModal')
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" maxlength="120" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Codigo</label>
                    <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" maxlength="50" placeholder="Se genera automaticamente si queda vacio">
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="activo" value="0">
                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="motivo-activo-create" checked>
                    <label class="form-check-label" for="motivo-activo-create">Activo</label>
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

@foreach ($motivos as $motivo)
    @can('ordenes-trabajo-motivos.editar')
    <div class="modal fade" id="editMotivoModal-{{ $motivo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('admin.ordenes-trabajo-motivos.update', $motivo->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Editar motivo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'editMotivoModal-' . $motivo->id)
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ session('open_modal') === 'editMotivoModal-' . $motivo->id ? old('nombre', $motivo->nombre) : $motivo->nombre }}" maxlength="120" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Codigo</label>
                        <input type="text" name="codigo" class="form-control" value="{{ session('open_modal') === 'editMotivoModal-' . $motivo->id ? old('codigo', $motivo->codigo) : $motivo->codigo }}" maxlength="50">
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" value="1" id="motivo-activo-{{ $motivo->id }}" @checked(session('open_modal') === 'editMotivoModal-' . $motivo->id ? old('activo', $motivo->activo) : $motivo->activo)>
                        <label class="form-check-label" for="motivo-activo-{{ $motivo->id }}">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    @can('ordenes-trabajo-motivos.eliminar')
    <div class="modal fade" id="deleteMotivoModal-{{ $motivo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('admin.ordenes-trabajo-motivos.destroy', $motivo->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Eliminar motivo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Confirma eliminar el motivo <strong>{{ $motivo->nombre }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
@endforeach

@if (session('open_modal'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById(@json(session('open_modal')));
                if (modal && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endpush
@endif
@endsection
