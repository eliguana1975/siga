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
                <h3>Tipos de verificaciones</h3>
                <p class="text-subtitle text-muted">
                    Configura los controles de vencimiento para verificaciones nacionales, provinciales, CNRT y otros certificados.
                </p>
            </div>
            <a href="{{ route('admin.verificaciones-tecnicas.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Nuevo tipo de verificacion</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.configuracion-vencimientos-verificacion.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label for="tipo" class="form-label">Tipo (*)</label>
                                <input type="text" name="tipo" id="tipo" class="form-control text-uppercase js-uppercase-input"
                                    value="{{ old('tipo') }}" list="tiposList"
                                    placeholder="EJ: CNRT" required>
                                <datalist id="tiposList">
                                    @foreach ($tipos as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </datalist>
                                @error('tipo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="nombre" class="form-label">Nombre (*)</label>
                                <input type="text" name="nombre" id="nombre" class="form-control text-uppercase js-uppercase-input"
                                    value="{{ old('nombre') }}" placeholder="Ej: CNRT" required>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="dias_alerta" class="form-label">Alerta dias (*)</label>
                                <input type="number" name="dias_alerta" id="dias_alerta"
                                    class="form-control" value="{{ old('dias_alerta', 30) }}" min="1" max="365" step="1" required>
                                @error('dias_alerta')
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
                                <textarea name="observaciones" id="observaciones" class="form-control text-uppercase js-uppercase-input" rows="2">{{ old('observaciones') }}</textarea>
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
                    <h4 class="card-title mb-0">Tipos configurados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.configuracion-vencimientos-verificacion.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar tipo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Tipo, nombre, estado u observaciones">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.configuracion-vencimientos-verificacion.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th>Alerta</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                    <th class="text-end" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($configuraciones as $configuracion)
                                    <tr>
                                        <td>{{ $configuracion->tipo }}</td>
                                        <td>{{ $configuracion->nombre }}</td>
                                        <td>{{ $configuracion->dias_alerta }} dias</td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$configuracion->estado] ?? 'bg-light-secondary' }}">
                                                {{ ucfirst($configuracion->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $configuracion->observaciones ?: '-' }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editVerificacion-{{ $configuracion->id }}" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('admin.configuracion-vencimientos-verificacion.destroy', $configuracion->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar este tipo de verificacion?');">
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
                                            No hay tipos configurados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($configuraciones->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $configuraciones->firstItem() }} a {{ $configuraciones->lastItem() }} de {{ $configuraciones->total() }} registros
                            </small>
                            <div>{{ $configuraciones->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($configuraciones as $configuracion)
        <div class="modal fade" id="editVerificacion-{{ $configuracion->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.configuracion-vencimientos-verificacion.update', $configuracion->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Editar tipo de verificacion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tipo (*)</label>
                                    <input type="text" name="tipo" class="form-control text-uppercase js-uppercase-input" value="{{ $configuracion->tipo }}" list="tiposList" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nombre (*)</label>
                                    <input type="text" name="nombre" class="form-control text-uppercase js-uppercase-input" value="{{ $configuracion->nombre }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Alerta dias (*)</label>
                                    <input type="number" name="dias_alerta" class="form-control" value="{{ $configuracion->dias_alerta }}" min="1" max="365" step="1" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Estado (*)</label>
                                    <select name="estado" class="form-select" required>
                                        <option value="activo" @selected($configuracion->estado === 'activo')>Activo</option>
                                        <option value="inactivo" @selected($configuracion->estado === 'inactivo')>Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" class="form-control text-uppercase js-uppercase-input" rows="3">{{ $configuracion->observaciones }}</textarea>
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

    @push('scripts')
        <script>
            document.addEventListener('input', function (event) {
                const input = event.target.closest('.js-uppercase-input');

                if (!input) {
                    return;
                }

                const start = input.selectionStart;
                const end = input.selectionEnd;
                input.value = input.value.toLocaleUpperCase('es-AR');

                if (start !== null && end !== null) {
                    input.setSelectionRange(start, end);
                }
            });
        </script>
    @endpush
@endsection
