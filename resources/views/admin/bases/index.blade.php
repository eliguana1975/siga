@extends('layouts.admin')

@push('styles')
    <style>
        .modal .input-group .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
        }

        .modal .input-group .input-group-text i {
            line-height: 1;
        }
    </style>
@endpush

@php
    $canCreateBases = auth()->user()?->can('bases.crear');
    $canEditBases = auth()->user()?->can('bases.editar');
    $canDeleteBases = auth()->user()?->can('bases.eliminar');
    $showBaseActions = $canEditBases || $canDeleteBases;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestión de Bases</h3>
                <p class="text-subtitle text-muted">
                    Administra las bases operativas registradas, sus datos de contacto y su estado.
                </p>
            </div>
            @if ($canCreateBases)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBaseModal">
                <i class="bi bi-plus-circle"></i> Nueva base
            </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de bases registradas</h4>
                </div>
                <div class="card-body">
                   <form method="GET" action="{{ route('admin.bases.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar base</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre, dirección o teléfono">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.bases.index') }}"
                                    class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'bases_registradas')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'bases_registradas')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'bases_registradas')">
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
                            Se encontraron {{ $bases->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    <th>Depósito</th>
                                    <th>Provincia</th>
                                    <th>Ciudad</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    @if ($showBaseActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bases as $base)
                                    <tr>
                                        <td>{{ $bases->firstItem() + $loop->index }}</td>
                                        <td>{{ $base->nombre }}</td>
                                        <td>{{ $base->deposito->nombre ?? 'N/A' }}</td>
                                        <td>{{ $base->provincia->nombre ?? 'N/A' }}</td>
                                        <td>{{ $base->ciudad->nombre ?? 'N/A' }}</td>
                                        <td>{{ $base->telefono ?: 'Sin teléfono' }}</td>
                                        <td>
                                            @if ($base->estado === 'activa')
                                                <span class="badge bg-light-success">Activa</span>
                                            @else
                                                <span class="badge bg-light-secondary">Inactiva</span>
                                            @endif
                                        </td>
                                        @if ($showBaseActions)
                                        <td class="text-end">
                                            @if ($canEditBases)
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editBaseModal-{{ $base->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @endif

                                            @if ($canDeleteBases)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteBaseModal-{{ $base->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showBaseActions ? 8 : 7 }}" class="text-center text-muted py-4">No hay bases registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($bases->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $bases->firstItem() }} a {{ $bases->lastItem() }} de
                                {{ $bases->total() }} registros
                            </small>
                            <div>
                                {{ $bases->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="createBaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.bases.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Crear base</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control"
                                    value="{{ old('nombre') }}" placeholder="Nombre de la base" required>
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" name="telefono" id="telefono" class="form-control"
                                    value="{{ old('telefono') }}" placeholder="Teléfono de la base">
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('telefono')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <label for="deposito_id" class="form-label">Depósito (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shop"></i></span>
                                <select name="deposito_id" id="deposito_id" class="form-select" required>
                                    <option value="">Seleccione un depósito</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected(old('deposito_id') == $deposito->id)>
                                            {{ $deposito->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('deposito_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="provincia_id" class="form-label">Provincia (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                <select name="provincia_id" id="provincia_id" class="form-select" required>
                                    <option value="">Seleccione una provincia</option>
                                    @foreach ($provincias as $provincia)
                                        <option value="{{ $provincia->id }}" @selected(old('provincia_id') == $provincia->id)>
                                            {{ $provincia->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('provincia_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="ciudad_id" class="form-label">Ciudad (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                <select name="ciudad_id" id="ciudad_id" class="form-select" required>
                                    <option value="">Seleccione una ciudad</option>
                                    @foreach ($ciudades as $ciudad)
                                        <option value="{{ $ciudad->id }}" @selected(old('ciudad_id') == $ciudad->id)>
                                            {{ $ciudad->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('ciudad_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="activa" @selected(old('estado', 'activa') === 'activa')>Activa</option>
                                    <option value="inactiva" @selected(old('estado') === 'inactiva')>Inactiva</option>
                                </select>
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('estado')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                <textarea name="direccion" id="direccion" class="form-control" rows="3"
                                    placeholder="Dirección de la base">{{ old('direccion') }}</textarea>
                            </div>
                            @if (session('open_modal') === 'createBaseModal')
                                @error('direccion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
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

    @foreach ($bases as $base)
        <div class="modal fade" id="editBaseModal-{{ $base->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.bases.update', $base->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar base</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="nombre-{{ $base->id }}" class="form-label">Nombre (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                                    <input type="text" name="nombre" id="nombre-{{ $base->id }}" class="form-control"
                                        value="{{ session('open_modal') === 'editBaseModal-' . $base->id ? old('nombre', $base->nombre) : $base->nombre }}"
                                        placeholder="Nombre de la base" required>
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('nombre')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="telefono-{{ $base->id }}" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                    <input type="text" name="telefono" id="telefono-{{ $base->id }}"
                                        class="form-control"
                                        value="{{ session('open_modal') === 'editBaseModal-' . $base->id ? old('telefono', $base->telefono) : $base->telefono }}"
                                        placeholder="Teléfono de la base">
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('telefono')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 mb-3">
                                <label for="deposito_id-{{ $base->id }}" class="form-label">Depósito (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shop"></i></span>
                                    <select name="deposito_id" id="deposito_id-{{ $base->id }}" class="form-select" required>
                                        <option value="">Seleccione un depósito</option>
                                        @foreach ($depositos as $deposito)
                                            @php
                                                $selectedDeposito = session('open_modal') === 'editBaseModal-' . $base->id
                                                    ? old('deposito_id', $base->deposito_id)
                                                    : $base->deposito_id;
                                            @endphp
                                            <option value="{{ $deposito->id }}" @selected($selectedDeposito == $deposito->id)>
                                                {{ $deposito->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('deposito_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="provincia_id-{{ $base->id }}" class="form-label">Provincia (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <select name="provincia_id" id="provincia_id-{{ $base->id }}" class="form-select" required>
                                        <option value="">Seleccione una provincia</option>
                                        @foreach ($provincias as $provincia)
                                            @php
                                                $selectedProvincia = session('open_modal') === 'editBaseModal-' . $base->id
                                                    ? old('provincia_id', $base->provincia_id)
                                                    : $base->provincia_id;
                                            @endphp
                                            <option value="{{ $provincia->id }}" @selected($selectedProvincia == $provincia->id)>
                                                {{ $provincia->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('provincia_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="ciudad_id-{{ $base->id }}" class="form-label">Ciudad (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <select name="ciudad_id" id="ciudad_id-{{ $base->id }}" class="form-select" required>
                                        <option value="">Seleccione una ciudad</option>
                                        @foreach ($ciudades as $ciudad)
                                            @php
                                                $selectedCiudad = session('open_modal') === 'editBaseModal-' . $base->id
                                                    ? old('ciudad_id', $base->ciudad_id)
                                                    : $base->ciudad_id;
                                            @endphp
                                            <option value="{{ $ciudad->id }}" @selected($selectedCiudad == $ciudad->id)>
                                                {{ $ciudad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('ciudad_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="estado-{{ $base->id }}" class="form-label">Estado (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                                    <select name="estado" id="estado-{{ $base->id }}" class="form-select" required>
                                        @php
                                            $selectedEstado = session('open_modal') === 'editBaseModal-' . $base->id
                                                ? old('estado', $base->estado)
                                                : $base->estado;
                                        @endphp
                                        <option value="activa" @selected($selectedEstado === 'activa')>Activa</option>
                                        <option value="inactiva" @selected($selectedEstado === 'inactiva')>Inactiva</option>
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('estado')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 mb-3">
                                <label for="direccion-{{ $base->id }}" class="form-label">Dirección</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <textarea name="direccion" id="direccion-{{ $base->id }}" class="form-control" rows="3"
                                        placeholder="Dirección de la base">{{ session('open_modal') === 'editBaseModal-' . $base->id ? old('direccion', $base->direccion) : $base->direccion }}</textarea>
                                </div>
                                @if (session('open_modal') === 'editBaseModal-' . $base->id)
                                    @error('direccion')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
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

        <div class="modal fade" id="deleteBaseModal-{{ $base->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.bases.destroy', $base->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar base</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">¿Está seguro de eliminar la base <strong>{{ $base->nombre }}</strong>?</p>
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
    </script>
@endpush
