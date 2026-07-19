@extends('layouts.admin')

@php
    $impuestosConfigurados = old('impuestos', $ajuste->impuestos ?? []);
    $selectedBackupTables = old('tablas', $tablasBackup ?? []);

    if (empty($impuestosConfigurados)) {
        $impuestosConfigurados = [
            ['nombre' => 'IVA', 'porcentaje' => 21, 'descripcion' => '', 'activo' => true],
            ['nombre' => 'Ingresos Brutos', 'porcentaje' => 0, 'descripcion' => '', 'activo' => true],
        ];
    }
@endphp

@section('content')
<div class="page-heading">
    <h4 class="card-title">Formulario de Ajustes</h4>
        <br>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-subtitle text-muted">Completa los datos de la empresa y carga el logo para previsualizarlo.</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ajustes.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $ajuste->nombre ?? '') }}" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre de la empresa">
                                        @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="cuit" class="form-label">CUIT</label>
                                        <input type="text" id="cuit" name="cuit" value="{{ old('cuit', $ajuste->cuit ?? '') }}" class="form-control @error('cuit') is-invalid @enderror" placeholder="CUIT de la empresa">
                                        @error('cuit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="4" placeholder="Descripción de la empresa">{{ old('descripcion', $ajuste->descripcion ?? '') }}</textarea>
                                    @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $ajuste->direccion ?? '') }}" class="form-control @error('direccion') is-invalid @enderror" placeholder="Dirección completa">
                                        @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="codigo_postal" class="form-label">Código postal</label>
                                        <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal', $ajuste->codigo_postal ?? '') }}" class="form-control @error('codigo_postal') is-invalid @enderror" placeholder="CP">
                                        @error('codigo_postal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $ajuste->telefono ?? '') }}" class="form-control @error('telefono') is-invalid @enderror" placeholder="Teléfono de contacto">
                                        @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">    
                                <div class="col-md-6">
                                    <label class="form-label">Provincia</label>
                                    @php $selectedProvincia = old('provincia_id', $ajuste->provincia_id ?? ''); @endphp
                                    <select name="provincia_id" class="form-select">
                                        <option value="">Seleccione provincia</option>
                                        @foreach ($provincias as $provincia)
                                        <option value="{{ $provincia->id }}" {{ (string)$provincia->id === (string)$selectedProvincia ? 'selected' : '' }}>{{ $provincia->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ciudad</label>
                                    @php $selectedCiudad = old('ciudad_id', $ajuste->ciudad_id ?? ''); @endphp
                                    <select name="ciudad_id" class="form-select">
                                        <option value="">Seleccione ciudad</option>
                                        @foreach ($ciudades as $ciudad)
                                        <option value="{{ $ciudad->id }}" {{ (string)$ciudad->id === (string)$selectedCiudad ? 'selected' : '' }}>{{ $ciudad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" id="email" name="email" value="{{ old('email', $ajuste->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" placeholder="correo@dominio.com">
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="web" class="form-label">Sitio web</label>
                                        <input type="url" id="web" name="web" value="{{ old('web', $ajuste->web ?? '') }}" class="form-control @error('web') is-invalid @enderror" placeholder="https://www.miempresa.com">
                                        @error('web')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="border rounded p-3 mt-2">
                                    <h5 class="mb-3">Configuracion operativa</h5>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-6">
                                            <label for="divisa" class="form-label">Divisa</label>
                                            <select id="divisa" name="divisa" class="form-control @error('divisa') is-invalid @enderror">
                                                <option value="">Selecciona una divisa</option>
                                            </select>
                                            @error('divisa')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex justify-content-between gap-3">
                                                    <div>
                                                        <label class="form-label mb-1" for="pedidos_automaticos_activos">Pedidos automaticos</label>
                                                        <p class="text-muted small mb-0">Genera pedidos pendientes cuando un articulo automatico llega al stock de pedido.</p>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input type="hidden" name="pedidos_automaticos_activos" value="0">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="pedidos_automaticos_activos"
                                                            name="pedidos_automaticos_activos" value="1"
                                                            @checked(old('pedidos_automaticos_activos', $ajuste->pedidos_automaticos_activos ?? false))>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border rounded p-3 mt-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                            <div>
                                                <h6 class="mb-1">Impuestos</h6>
                                                <p class="text-muted small mb-0">Carga IVA, Ingresos Brutos, percepciones u otros impuestos generales.</p>
                                            </div>
                                            <button type="button" id="addTaxBtn" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle"></i> Agregar impuesto
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width: 180px;">Nombre</th>
                                                        <th style="width: 140px;">Porcentaje</th>
                                                        <th style="min-width: 220px;">Descripcion</th>
                                                        <th style="width: 90px;">Activo</th>
                                                        <th style="width: 60px;" class="text-end"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="taxRows">
                                                    @foreach ($impuestosConfigurados as $index => $impuesto)
                                                        <tr data-tax-row>
                                                            <td>
                                                                <input type="text" name="impuestos[{{ $index }}][nombre]"
                                                                    value="{{ $impuesto['nombre'] ?? '' }}"
                                                                    class="form-control form-control-sm @error("impuestos.$index.nombre") is-invalid @enderror"
                                                                    placeholder="IVA">
                                                            </td>
                                                            <td>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="text" name="impuestos[{{ $index }}][porcentaje]"
                                                                        value="{{ $impuesto['porcentaje'] ?? '' }}"
                                                                        class="form-control @error("impuestos.$index.porcentaje") is-invalid @enderror"
                                                                        inputmode="decimal" placeholder="21">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="impuestos[{{ $index }}][descripcion]"
                                                                    value="{{ $impuesto['descripcion'] ?? '' }}"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Opcional">
                                                            </td>
                                                            <td>
                                                                <input type="hidden" name="impuestos[{{ $index }}][activo]" value="0">
                                                                <div class="form-check form-switch mb-0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="impuestos[{{ $index }}][activo]" value="1"
                                                                        @checked((bool) ($impuesto['activo'] ?? true))>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <button type="button" class="btn btn-sm btn-light-danger" data-remove-tax title="Eliminar">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @error('impuestos')
                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">Guardar ajustes</button>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card p-3">
                                    <h5 class="card-title">Logo para informes</h5>
                                    <div class="mb-3 text-center">
                                        <img id="logoPreview" src="{{ $ajuste && $ajuste->logo ? asset('storage/' . $ajuste->logo) : asset('assets/static/images/default-logo.png') }}" class="img-fluid rounded" style="max-height: 260px; width: 100%; object-fit: contain;">
                                    </div>
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Cargar logo</label>
                                        <input type="file" id="logo" name="logo" accept="image/*" class="form-control @error('logo') is-invalid @enderror">
                                        @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <p class="text-muted small">Este logo se usa para informes, comprobantes e impresiones.</p>
                                </div>

                                <div class="card p-3 mt-3">
                                    <h5 class="card-title">Imagen del login</h5>
                                    <div class="mb-3 text-center">
                                        <img id="loginImagePreview" src="{{ $ajuste && $ajuste->imagen_login ? asset('storage/' . $ajuste->imagen_login) : asset('assets/static/images/default-logo.png') }}" class="img-fluid rounded" style="max-height: 260px; width: 100%; object-fit: contain;">
                                    </div>
                                    <div class="mb-3">
                                        <label for="imagen_login" class="form-label">Cargar imagen de login</label>
                                        <input type="file" id="imagen_login" name="imagen_login" accept="image/*" class="form-control @error('imagen_login') is-invalid @enderror">
                                        @error('imagen_login')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <p class="text-muted small">Esta imagen se muestra en el panel azul del login. Es independiente del logo de informes.</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bancos</h4>
                    <p class="text-muted mb-0">Administra los bancos disponibles para seleccionar en pagos con cheque o ECheq.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bancos.store') }}" class="row g-3 align-items-end mb-4">
                        @csrf
                        <div class="col-12 col-md-8">
                            <label class="form-label">Nombre del banco</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Banco">
                        </div>
                        <div class="col-12 col-md-2">
                            <input type="hidden" name="activo" value="0">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="activo" value="1" id="nuevoBancoActivo" checked>
                                <label class="form-check-label" for="nuevoBancoActivo">Activo</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Agregar
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Banco</th>
                                    <th style="width: 120px;">Estado</th>
                                    <th style="width: 210px;" class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bancos as $banco)
                                    <tr>
                                        <td>
                                            <form id="banco-form-{{ $banco->id }}" method="POST" action="{{ route('admin.bancos.update', $banco->id) }}" class="d-flex gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="nombre" class="form-control form-control-sm" value="{{ $banco->nombre }}" required>
                                                <input type="hidden" name="activo" value="0">
                                            </form>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" form="banco-form-{{ $banco->id }}" type="checkbox" name="activo" value="1" @checked($banco->activo)>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="submit" form="banco-form-{{ $banco->id }}" class="btn btn-sm btn-primary" title="Guardar">
                                                <i class="bi bi-save"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.bancos.destroy', $banco->id) }}" class="d-inline" onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar este banco?');">
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
                                        <td colspan="3" class="text-center text-muted py-4">No hay bancos cargados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Backup de base de datos</h4>
                    <p class="text-muted mb-0">Selecciona una o mas tablas y descarga un archivo SQL para respaldo.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ajustes.backup') }}">
                        @csrf

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-light-primary" id="backupSelectAll">Seleccionar todo</button>
                            <button type="button" class="btn btn-sm btn-light-secondary" id="backupClearAll">Limpiar seleccion</button>
                        </div>

                        <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
                            <div class="row g-2">
                                @forelse ($tablasBackup as $tabla)
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="form-check">
                                            <input class="form-check-input backup-table-checkbox" type="checkbox" name="tablas[]" value="{{ $tabla }}"
                                                id="backup-table-{{ $loop->index }}" @checked(in_array($tabla, $selectedBackupTables, true))>
                                            <label class="form-check-label" for="backup-table-{{ $loop->index }}">{{ $tabla }}</label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No se encontraron tablas para respaldar.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @error('tablas')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-download"></i> Descargar backup SQL
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoInput = document.getElementById('logo');
        const logoPreview = document.getElementById('logoPreview');
        const loginImageInput = document.getElementById('imagen_login');
        const loginImagePreview = document.getElementById('loginImagePreview');
        const divisaSelect = document.getElementById('divisa');
        const addTaxBtn = document.getElementById('addTaxBtn');
        const taxRows = document.getElementById('taxRows');
        const selectedCurrency = "{{ old('divisa', $ajuste->divisa ?? '') }}";
        const backupCheckboxes = document.querySelectorAll('.backup-table-checkbox');
        const backupSelectAllBtn = document.getElementById('backupSelectAll');
        const backupClearAllBtn = document.getElementById('backupClearAll');

        if (logoInput && logoPreview) {
            logoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        if (loginImageInput && loginImagePreview) {
            loginImageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    loginImagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        if (divisaSelect) {
            fetch("{{ asset('assets/divisas.json') }}")
                .then((response) => response.ok ? response.json() : Promise.reject(response))
                .then((data) => {
                    const currencies = Object.keys(data).sort();
                    let selectedFound = false;
                    currencies.forEach((code) => {
                        const option = document.createElement('option');
                        option.value = code;
                        option.textContent = `${code} – ${data[code].name}`;
                        if (code === selectedCurrency) {
                            option.selected = true;
                            selectedFound = true;
                        }
                        divisaSelect.appendChild(option);
                    });
                    if (selectedCurrency && !selectedFound) {
                        const option = document.createElement('option');
                        option.value = selectedCurrency;
                        option.textContent = selectedCurrency;
                        option.selected = true;
                        divisaSelect.insertBefore(option, divisaSelect.firstChild);
                    }
                })
                .catch(() => {
                    console.warn('No se pudo cargar la lista de divisas.');
                });
        }

        function taxRowTemplate(index) {
            return `
                <tr data-tax-row>
                    <td>
                        <input type="text" name="impuestos[${index}][nombre]" class="form-control form-control-sm" placeholder="IVA">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="text" name="impuestos[${index}][porcentaje]" class="form-control" inputmode="decimal" placeholder="21">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="impuestos[${index}][descripcion]" class="form-control form-control-sm" placeholder="Opcional">
                    </td>
                    <td>
                        <input type="hidden" name="impuestos[${index}][activo]" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="impuestos[${index}][activo]" value="1" checked>
                        </div>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-light-danger" data-remove-tax title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }

        function reindexTaxRows() {
            taxRows?.querySelectorAll('[data-tax-row]').forEach((row, index) => {
                row.querySelectorAll('[name^="impuestos["]').forEach((input) => {
                    input.name = input.name.replace(/impuestos\[\d+\]/, `impuestos[${index}]`);
                });
            });
        }

        addTaxBtn?.addEventListener('click', function() {
            const index = taxRows.querySelectorAll('[data-tax-row]').length;
            taxRows.insertAdjacentHTML('beforeend', taxRowTemplate(index));
        });

        taxRows?.addEventListener('click', function(event) {
            const button = event.target.closest('[data-remove-tax]');

            if (!button) {
                return;
            }

            button.closest('[data-tax-row]')?.remove();
            reindexTaxRows();
        });

        backupSelectAllBtn?.addEventListener('click', function() {
            backupCheckboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
        });

        backupClearAllBtn?.addEventListener('click', function() {
            backupCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        });
    });
</script>
@endpush
@endsection
