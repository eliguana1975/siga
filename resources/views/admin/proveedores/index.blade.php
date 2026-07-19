@extends('layouts.admin')

@php
    $canCreateProveedores = auth()->user()?->can('proveedores.crear');
    $canEditProveedores = auth()->user()?->can('proveedores.editar');
    $canDeleteProveedores = auth()->user()?->can('proveedores.eliminar');
    $showProveedorActions = $canEditProveedores || $canDeleteProveedores;
    $formasPago = \App\Models\Proveedor::formasPago();
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Proveedores</h3>
                <p class="text-subtitle text-muted">Administra los proveedores, sus datos de contacto y ubicacion.</p>
            </div>
            @if ($canCreateProveedores)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProveedorModal">
                <i class="bi bi-plus-circle"></i> Nuevo proveedor
            </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de proveedores</h4>
                </div>
                <div class="card-body">
                

                    <form method="GET" action="{{ route('admin.proveedores.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="search" class="form-label mb-1">Buscar proveedor</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre, telefono, email, contacto...">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.proveedores.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'proveedores_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'proveedores_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'proveedores_registrados')">
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
                            Se encontraron {{ $proveedores->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    <th>Telefono</th>
                                    <th>Email</th>
                                    <th>Provincia</th>
                                    <th>Ciudad</th>
                                    <th>Codigo postal</th>
                                    <th>Contacto</th>
                                    <th>Forma de pago</th>
                                    <th>Impuestos</th>
                                    @if ($showProveedorActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proveedores as $proveedor)
                                    <tr>
                                        <td>{{ $proveedores->firstItem() + $loop->index }}</td>
                                        <td>{{ $proveedor->nombre }}</td>
                                        <td>{{ $proveedor->telefono }}</td>
                                        <td>{{ $proveedor->email }}</td>
                                        <td>{{ $proveedor->provincia?->nombre }}</td>
                                        <td>{{ $proveedor->ciudad?->nombre }}</td>
                                        <td>{{ $proveedor->codigo_postal }}</td>
                                        <td>{{ $proveedor->contacto }}</td>
                                        <td>{{ $proveedor->formaPagoPreferidaLabel() }}</td>
                                        <td>
                                            @forelse ($proveedor->impuestosActivos() as $impuesto)
                                                <span class="badge bg-light-info">
                                                    {{ $impuesto['nombre'] }} {{ number_format((float) ($impuesto['porcentaje'] ?? 0), 2, ',', '.') }}%
                                                </span>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        @if ($showProveedorActions)
                                        <td class="text-end">
                                            @if ($canEditProveedores)
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editProveedorModal-{{ $proveedor->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @endif
                                            @if ($canDeleteProveedores)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteProveedorModal-{{ $proveedor->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showProveedorActions ? 11 : 10 }}" class="text-center text-muted py-4">
                                            No hay proveedores registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($proveedores->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $proveedores->firstItem() }} a {{ $proveedores->lastItem() }} de {{ $proveedores->total() }} registros
                            </small>
                            <div>
                                {{ $proveedores->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="createProveedorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.proveedores.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Nuevo proveedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'createProveedorModal')
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
                            <label class="form-label">Nombre (*)</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contacto</label>
                            <input type="text" name="contacto" class="form-control" value="{{ old('contacto') }}" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Forma de pago preferida</label>
                            <select name="forma_pago_preferida" class="form-select">
                                <option value="">Sin definir</option>
                                @foreach ($formasPago as $value => $label)
                                    <option value="{{ $value }}" @selected(old('forma_pago_preferida') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Condicion de pago en dias</label>
                            <input type="text" name="condicion_pago_dias" class="form-control" value="{{ old('condicion_pago_dias') }}" placeholder="30-60-90" maxlength="80">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provincia</label>
                            <select name="provincia_id" class="form-select">
                                <option value="">Seleccione provincia</option>
                                @foreach ($provincias as $provincia)
                                    <option value="{{ $provincia->id }}" @selected((string) old('provincia_id') === (string) $provincia->id)>
                                        {{ $provincia->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ciudad</label>
                            <select name="ciudades_id" class="form-select">
                                <option value="">Seleccione ciudad</option>
                                @foreach ($ciudades as $ciudad)
                                    <option value="{{ $ciudad->id }}" @selected((string) old('ciudades_id') === (string) $ciudad->id)>
                                        {{ $ciudad->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Direccion</label>
                            <textarea name="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Codigo postal</label>
                            <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal') }}" maxlength="20">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Datos de pago</label>
                            <textarea name="datos_pago" class="form-control" rows="2" placeholder="CBU, alias, cuenta, banco o indicaciones para el pago">{{ old('datos_pago') }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <label class="form-label mb-0">Impuestos del proveedor</label>
                                        <p class="text-muted small mb-0">Percepciones, retenciones u otros impuestos propios de este proveedor.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary add-supplier-tax">
                                        <i class="bi bi-plus-circle"></i> Agregar
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th style="width: 130px;">%</th>
                                                <th>Descripcion</th>
                                                <th style="width: 90px;">Activo</th>
                                                <th style="width: 55px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="supplier-tax-rows">
                                            @foreach (old('impuestos', []) as $taxIndex => $impuesto)
                                                <tr data-supplier-tax-row>
                                                    <td><input type="text" name="impuestos[{{ $taxIndex }}][nombre]" class="form-control form-control-sm" value="{{ $impuesto['nombre'] ?? '' }}"></td>
                                                    <td><input type="text" name="impuestos[{{ $taxIndex }}][porcentaje]" class="form-control form-control-sm" value="{{ $impuesto['porcentaje'] ?? '' }}" inputmode="decimal"></td>
                                                    <td><input type="text" name="impuestos[{{ $taxIndex }}][descripcion]" class="form-control form-control-sm" value="{{ $impuesto['descripcion'] ?? '' }}"></td>
                                                    <td>
                                                        <input type="hidden" name="impuestos[{{ $taxIndex }}][activo]" value="0">
                                                        <input type="checkbox" class="form-check-input" name="impuestos[{{ $taxIndex }}][activo]" value="1" @checked((bool) ($impuesto['activo'] ?? true))>
                                                    </td>
                                                    <td><button type="button" class="btn btn-sm btn-light-danger remove-supplier-tax"><i class="bi bi-trash"></i></button></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control" rows="3">{{ old('notas') }}</textarea>
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

    @foreach ($proveedores as $proveedor)
        <div class="modal fade" id="editProveedorModal-{{ $proveedor->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.proveedores.update', $proveedor->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar proveedor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any() && session('open_modal') === 'editProveedorModal-' . $proveedor->id)
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $selectedProvinciaId = session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('provincia_id', $proveedor->provincia_id) : $proveedor->provincia_id;
                            $selectedCiudadId = session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('ciudades_id', $proveedor->ciudades_id) : $proveedor->ciudades_id;
                        @endphp

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre (*)</label>
                                <input type="text" name="nombre" class="form-control"
                                    value="{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('nombre', $proveedor->nombre) : $proveedor->nombre }}"
                                    maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contacto</label>
                                <input type="text" name="contacto" class="form-control"
                                    value="{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('contacto', $proveedor->contacto) : $proveedor->contacto }}"
                                    maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefono</label>
                                <input type="text" name="telefono" class="form-control"
                                    value="{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('telefono', $proveedor->telefono) : $proveedor->telefono }}"
                                    maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('email', $proveedor->email) : $proveedor->email }}"
                                    maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Forma de pago preferida</label>
                                <select name="forma_pago_preferida" class="form-select">
                                    <option value="">Sin definir</option>
                                    @foreach ($formasPago as $value => $label)
                                        <option value="{{ $value }}" @selected((session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('forma_pago_preferida', $proveedor->forma_pago_preferida) : $proveedor->forma_pago_preferida) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Condicion de pago en dias</label>
                                <input type="text" name="condicion_pago_dias" class="form-control"
                                    value="{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('condicion_pago_dias', $proveedor->condicion_pago_dias) : $proveedor->condicion_pago_dias }}"
                                    placeholder="30-60-90" maxlength="80">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Provincia</label>
                                <select name="provincia_id" class="form-select">
                                    <option value="">Seleccione provincia</option>
                                    @foreach ($provincias as $provincia)
                                        <option value="{{ $provincia->id }}" @selected((string) $selectedProvinciaId === (string) $provincia->id)>
                                            {{ $provincia->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ciudad</label>
                                <select name="ciudades_id" class="form-select">
                                    <option value="">Seleccione ciudad</option>
                                    @foreach ($ciudades as $ciudad)
                                        <option value="{{ $ciudad->id }}" @selected((string) $selectedCiudadId === (string) $ciudad->id)>
                                            {{ $ciudad->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Direccion</label>
                                <textarea name="direccion" class="form-control" rows="2">{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('direccion', $proveedor->direccion) : $proveedor->direccion }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Codigo postal</label>
                                <input type="text" name="codigo_postal" class="form-control"
                                    value="{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('codigo_postal', $proveedor->codigo_postal) : $proveedor->codigo_postal }}"
                                    maxlength="20">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Datos de pago</label>
                                <textarea name="datos_pago" class="form-control" rows="2" placeholder="CBU, alias, cuenta, banco o indicaciones para el pago">{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('datos_pago', $proveedor->datos_pago) : $proveedor->datos_pago }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <div>
                                            <label class="form-label mb-0">Impuestos del proveedor</label>
                                            <p class="text-muted small mb-0">Percepciones, retenciones u otros impuestos propios de este proveedor.</p>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary add-supplier-tax">
                                            <i class="bi bi-plus-circle"></i> Agregar
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th style="width: 130px;">%</th>
                                                    <th>Descripcion</th>
                                                    <th style="width: 90px;">Activo</th>
                                                    <th style="width: 55px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="supplier-tax-rows">
                                                @php
                                                    $editTaxes = session('open_modal') === 'editProveedorModal-' . $proveedor->id
                                                        ? old('impuestos', $proveedor->impuestos ?? [])
                                                        : ($proveedor->impuestos ?? []);
                                                @endphp
                                                @foreach ($editTaxes as $taxIndex => $impuesto)
                                                    <tr data-supplier-tax-row>
                                                        <td><input type="text" name="impuestos[{{ $taxIndex }}][nombre]" class="form-control form-control-sm" value="{{ $impuesto['nombre'] ?? '' }}"></td>
                                                        <td><input type="text" name="impuestos[{{ $taxIndex }}][porcentaje]" class="form-control form-control-sm" value="{{ $impuesto['porcentaje'] ?? '' }}" inputmode="decimal"></td>
                                                        <td><input type="text" name="impuestos[{{ $taxIndex }}][descripcion]" class="form-control form-control-sm" value="{{ $impuesto['descripcion'] ?? '' }}"></td>
                                                        <td>
                                                            <input type="hidden" name="impuestos[{{ $taxIndex }}][activo]" value="0">
                                                            <input type="checkbox" class="form-check-input" name="impuestos[{{ $taxIndex }}][activo]" value="1" @checked((bool) ($impuesto['activo'] ?? true))>
                                                        </td>
                                                        <td><button type="button" class="btn btn-sm btn-light-danger remove-supplier-tax"><i class="bi bi-trash"></i></button></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notas</label>
                                <textarea name="notas" class="form-control" rows="3">{{ session('open_modal') === 'editProveedorModal-' . $proveedor->id ? old('notas', $proveedor->notas) : $proveedor->notas }}</textarea>
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

        <div class="modal fade" id="deleteProveedorModal-{{ $proveedor->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.proveedores.destroy', $proveedor->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar proveedor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">¿Está seguro de eliminar el proveedor <strong>{{ $proveedor->nombre }}</strong>?</p>
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

        function supplierTaxRowTemplate(index) {
            return `
                <tr data-supplier-tax-row>
                    <td><input type="text" name="impuestos[${index}][nombre]" class="form-control form-control-sm" placeholder="Percepcion IVA"></td>
                    <td><input type="text" name="impuestos[${index}][porcentaje]" class="form-control form-control-sm" inputmode="decimal" placeholder="3,00"></td>
                    <td><input type="text" name="impuestos[${index}][descripcion]" class="form-control form-control-sm" placeholder="Opcional"></td>
                    <td>
                        <input type="hidden" name="impuestos[${index}][activo]" value="0">
                        <input type="checkbox" class="form-check-input" name="impuestos[${index}][activo]" value="1" checked>
                    </td>
                    <td><button type="button" class="btn btn-sm btn-light-danger remove-supplier-tax"><i class="bi bi-trash"></i></button></td>
                </tr>
            `;
        }

        function reindexSupplierTaxRows(container) {
            container.querySelectorAll('[data-supplier-tax-row]').forEach((row, index) => {
                row.querySelectorAll('[name^="impuestos["]').forEach(input => {
                    input.name = input.name.replace(/impuestos\[\d+\]/, `impuestos[${index}]`);
                });
            });
        }

        document.addEventListener('click', function(event) {
            const addButton = event.target.closest('.add-supplier-tax');
            const removeButton = event.target.closest('.remove-supplier-tax');

            if (addButton) {
                const container = addButton.closest('.border')?.querySelector('.supplier-tax-rows');

                if (!container) {
                    return;
                }

                const index = container.querySelectorAll('[data-supplier-tax-row]').length;
                container.insertAdjacentHTML('beforeend', supplierTaxRowTemplate(index));
            }

            if (removeButton) {
                const container = removeButton.closest('.supplier-tax-rows');
                removeButton.closest('[data-supplier-tax-row]')?.remove();

                if (container) {
                    reindexSupplierTaxRows(container);
                }
            }
        });

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
