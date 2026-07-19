@extends('layouts.admin')

@php
    $estadoBadges = [
        'activo' => 'bg-light-success',
        'inactivo' => 'bg-light-secondary',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Intervalos de servicios</h3>
                <p class="text-subtitle text-muted">
                    Configura cada cuanto se realizan los servicios por kilometros u horas de trabajo.
                </p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Nuevo intervalo</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.configuracion-intervalos-servicio.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label for="sistema" class="form-label">Sistema (*)</label>
                                <input type="text" name="sistema" id="sistema" class="form-control"
                                    value="{{ old('sistema') }}" list="sistemasList"
                                    placeholder="Ej: Motor, Caja manual, Direccion" required>
                                <datalist id="sistemasList">
                                    @foreach ($sistemas as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                    @foreach ($intervalos->pluck('sistema')->unique() as $sistemaExistente)
                                        <option value="{{ $sistemaExistente }}">{{ $sistemas[$sistemaExistente] ?? $sistemaExistente }}</option>
                                    @endforeach
                                </datalist>
                                @error('sistema')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="nombre" class="form-label">Nombre (*)</label>
                                <input type="text" name="nombre" id="nombre" class="form-control"
                                    value="{{ old('nombre') }}" placeholder="Ej: Diferencial delantero" required>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="kilometros_intervalo" class="form-label">Intervalo (*)</label>
                                <input type="number" name="kilometros_intervalo" id="kilometros_intervalo"
                                    class="form-control" value="{{ old('kilometros_intervalo') }}" min="1" step="1" required>
                                @error('kilometros_intervalo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="unidad_intervalo" class="form-label">Unidad (*)</label>
                                <select name="unidad_intervalo" id="unidad_intervalo" class="form-select" required>
                                    @foreach ($unidades as $value => $label)
                                        <option value="{{ $value }}" @selected(old('unidad_intervalo', 'km') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('unidad_intervalo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="estado" class="form-label">Estado (*)</label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="activo" @selected(old('estado', 'activo') === 'activo')>Activo</option>
                                    <option value="inactivo" @selected(old('estado') === 'inactivo')>Inactivo</option>
                                </select>
                                @error('estado')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Agregar
                                </button>
                            </div>
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Intervalos configurados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.configuracion-intervalos-servicio.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar intervalo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Sistema, nombre, estado u observaciones">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.configuracion-intervalos-servicio.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Sistema</th>
                                    <th>Servicio</th>
                                    <th>Frecuencia</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                    <th class="text-end" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($intervalos as $intervalo)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light-primary">{{ $intervalo->sistemaLabel() }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $intervalo->nombre }}</div>
                                            <small class="text-muted">ID {{ $intervalo->id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $intervalo->unidadCorta() === 'hs' ? 'bg-light-warning' : 'bg-light-info' }}">
                                                Cada {{ number_format((int) $intervalo->kilometros_intervalo, 0, ',', '.') }} {{ $intervalo->unidadCorta() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$intervalo->estado] ?? 'bg-light-secondary' }}">
                                                {{ ucfirst($intervalo->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $intervalo->observaciones ?: '-' }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editIntervalo-{{ $intervalo->id }}" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('admin.configuracion-intervalos-servicio.destroy', $intervalo->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar este intervalo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No hay intervalos configurados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($intervalos->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $intervalos->firstItem() }} a {{ $intervalos->lastItem() }} de {{ $intervalos->total() }} registros
                            </small>
                            <div>{{ $intervalos->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($intervalos as $intervalo)
        <div class="modal fade" id="editIntervalo-{{ $intervalo->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.configuracion-intervalos-servicio.update', $intervalo->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Editar intervalo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="sistema-{{ $intervalo->id }}" class="form-label">Sistema (*)</label>
                                    <input type="text" name="sistema" id="sistema-{{ $intervalo->id }}"
                                        class="form-control" value="{{ $intervalo->sistema }}"
                                        list="sistemasList" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="nombre-{{ $intervalo->id }}" class="form-label">Nombre (*)</label>
                                    <input type="text" name="nombre" id="nombre-{{ $intervalo->id }}" class="form-control"
                                        value="{{ $intervalo->nombre }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="kilometros-{{ $intervalo->id }}" class="form-label">Intervalo (*)</label>
                                    <input type="number" name="kilometros_intervalo" id="kilometros-{{ $intervalo->id }}"
                                        class="form-control" value="{{ $intervalo->kilometros_intervalo }}" min="1" step="1" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="unidad-{{ $intervalo->id }}" class="form-label">Unidad (*)</label>
                                    <select name="unidad_intervalo" id="unidad-{{ $intervalo->id }}" class="form-select" required>
                                        @foreach ($unidades as $value => $label)
                                            <option value="{{ $value }}" @selected(($intervalo->unidad_intervalo ?? 'km') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="estado-{{ $intervalo->id }}" class="form-label">Estado (*)</label>
                                    <select name="estado" id="estado-{{ $intervalo->id }}" class="form-select" required>
                                        <option value="activo" @selected($intervalo->estado === 'activo')>Activo</option>
                                        <option value="inactivo" @selected($intervalo->estado === 'inactivo')>Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="observaciones-{{ $intervalo->id }}" class="form-label">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones-{{ $intervalo->id }}" class="form-control" rows="3">{{ $intervalo->observaciones }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
