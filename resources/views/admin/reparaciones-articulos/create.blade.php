@extends('layouts.admin')

@php
    $proveedorOptions = $proveedores->map(fn ($proveedor) => [
        'id' => $proveedor->id,
        'provincia_id' => $proveedor->provincia_id,
        'provincia_nombre' => $proveedor->provincia?->nombre,
        'ciudad_id' => $proveedor->ciudades_id,
        'ciudad_nombre' => $proveedor->ciudad?->nombre,
        'direccion' => $proveedor->direccion,
        'telefono' => $proveedor->telefono,
        'codigo_postal' => $proveedor->codigo_postal,
    ])->values();

    $articuloOptions = $articulos->map(fn ($articulo) => [
        'id' => $articulo->id,
        'label' => trim($articulo->nombre . ($articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '')),
    ])->values();
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Nueva orden de reparacion</h3>
                <p class="text-subtitle text-muted">Registra articulos enviados a proveedor para reparacion y seguimiento.</p>
            </div>
            <a href="{{ route('admin.reparaciones-articulos.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reparaciones-articulos.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <div id="proveedorDataWarning" class="alert alert-warning py-2 mb-0 d-none" role="alert"></div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Datos de quien remite (Empresa)</h6>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Empresa</label>
                                            <input type="text" class="form-control" value="{{ $empresaRemite['nombre'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">CUIT</label>
                                            <input type="text" class="form-control" value="{{ $empresaRemite['cuit'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Codigo postal</label>
                                            <input type="text" class="form-control" value="{{ $empresaRemite['codigo_postal'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">Telefono</label>
                                            <input type="text" class="form-control" value="{{ $empresaRemite['telefono'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12 col-md-12">
                                            <label class="form-label">Direccion</label>
                                            <input type="text" class="form-control" value="{{ $empresaRemite['direccion'] ?? '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="mb-0">Datos de quien recibe (Proveedor)</h6>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Proveedor que recibe (*)</label>
                                <select id="proveedor_id" name="proveedor_id" class="form-select" required>
                                    <option value="">Seleccione proveedor</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}"
                                            data-provincia-id="{{ $proveedor->provincia_id }}"
                                            data-provincia-nombre="{{ $proveedor->provincia?->nombre }}"
                                            data-ciudad-id="{{ $proveedor->ciudades_id }}"
                                            data-ciudad-nombre="{{ $proveedor->ciudad?->nombre }}"
                                            data-direccion="{{ $proveedor->direccion }}"
                                            data-telefono="{{ $proveedor->telefono }}"
                                            data-codigo-postal="{{ $proveedor->codigo_postal }}"
                                            @selected((string) old('proveedor_id') === (string) $proveedor->id)>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha envio (*)</label>
                                <input type="date" name="fecha_envio" class="form-control" value="{{ old('fecha_envio', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha compromiso</label>
                                <input type="date" name="fecha_compromiso" class="form-control" value="{{ old('fecha_compromiso') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Provincia proveedor</label>
                                <input id="provincia_nombre" type="text" class="form-control" value="" readonly>
                                <input id="provincia_id" type="hidden" name="provincia_id" value="{{ old('provincia_id') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Ciudad proveedor</label>
                                <input id="ciudad_nombre" type="text" class="form-control" value="" readonly>
                                <input id="ciudad_id" type="hidden" name="ciudad_id" value="{{ old('ciudad_id') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Telefono proveedor</label>
                                <input id="telefono" type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" maxlength="50" readonly>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label">Domicilio proveedor</label>
                                <input id="domicilio" type="text" name="domicilio" class="form-control" value="{{ old('domicilio') }}" maxlength="255" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Codigo postal proveedor</label>
                                <input id="codigo_postal" type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal') }}" maxlength="20" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones generales</label>
                                <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>

                        <div class="border rounded p-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0">Detalle de articulos enviados</h5>
                            </div>
                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Articulo (*)</label>
                                    <select id="detalle_articulo_id" class="form-select form-select-sm js-select2" data-icon-decorated="true" data-placeholder="Escriba para buscar articulo">
                                        <option value="">Seleccione articulo</option>
                                        @foreach ($articulos as $articulo)
                                            <option value="{{ $articulo->id }}">{{ trim($articulo->nombre . ($articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '')) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-lg-2">
                                    <label class="form-label">Cantidad (*)</label>
                                    <input id="detalle_cantidad" type="number" min="1" value="1" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-lg-2">
                                    <button type="button" id="addRow" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Agregar
                                    </button>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <button type="button" class="btn btn-sm btn-outline-info w-100" data-bs-toggle="collapse"
                                        data-bs-target="#manualArticuloReparacion" aria-expanded="false"
                                        aria-controls="manualArticuloReparacion" title="Agregar articulo manual">
                                        <i class="bi bi-plus-square"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="collapse mb-3" id="manualArticuloReparacion">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label">Articulo manual</label>
                                        <input id="detalle_descripcion_manual" type="text" class="form-control form-control-sm" maxlength="255" placeholder="Si no existe en lista">
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <label class="form-label">Codigo manual</label>
                                        <input id="detalle_codigo_manual" type="text" class="form-control form-control-sm" maxlength="120" placeholder="Opcional">
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <label class="form-label">Costo unit.</label>
                                        <input id="detalle_costo" type="number" min="0" step="0.01" class="form-control form-control-sm" placeholder="0.00">
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label">Observaciones</label>
                                        <input id="detalle_observaciones" type="text" class="form-control form-control-sm" maxlength="255" placeholder="Detalle de falla">
                                    </div>
                                </div>
                            </div>
                            <small id="detalle-feedback" class="text-muted d-block mb-2">
                                Agrega articulos desde la lista o, si no existe, cargalo en el bloque manual.
                            </small>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Articulo</th>
                                            <th style="width: 150px;">Codigo</th>
                                            <th style="width: 130px;">Cant.</th>
                                            <th style="width: 160px;">Costo unit.</th>
                                            <th>Observaciones</th>
                                            <th style="width: 55px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalleRows"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.reparaciones-articulos.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar orden</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script type="application/json" id="reparacion-proveedores-data">@json($proveedorOptions)</script>
    <script type="application/json" id="reparacion-articulos-data">@json($articuloOptions)</script>
@endsection

@push('scripts')
    <script>
        const initReparacionArticuloCreate = function () {
            const proveedores = JSON.parse(document.getElementById('reparacion-proveedores-data')?.textContent || '[]');
            const articulos = JSON.parse(document.getElementById('reparacion-articulos-data')?.textContent || '[]');

            const proveedorSelect = document.getElementById('proveedor_id');
            const provinciaInput = document.getElementById('provincia_nombre');
            const provinciaIdInput = document.getElementById('provincia_id');
            const ciudadInput = document.getElementById('ciudad_nombre');
            const ciudadIdInput = document.getElementById('ciudad_id');
            const domicilioInput = document.getElementById('domicilio');
            const telefonoInput = document.getElementById('telefono');
            const codigoPostalInput = document.getElementById('codigo_postal');
            const proveedorDataWarning = document.getElementById('proveedorDataWarning');
            const detalleRows = document.getElementById('detalleRows');
            const addRowButton = document.getElementById('addRow');
            const detalleArticuloId = document.getElementById('detalle_articulo_id');
            const detalleDescripcionManual = document.getElementById('detalle_descripcion_manual');
            const detalleCodigoManual = document.getElementById('detalle_codigo_manual');
            const detalleCantidad = document.getElementById('detalle_cantidad');
            const detalleCosto = document.getElementById('detalle_costo');
            const detalleObservaciones = document.getElementById('detalle_observaciones');
            const detalleFeedback = document.getElementById('detalle-feedback');

            const proveedorById = new Map(
                proveedores.map((proveedor) => [String(proveedor.id), proveedor])
            );

            const upperText = function (value) {
                return String(value || '').toLocaleUpperCase('es-AR');
            };

            const normalizeUppercaseFormInputs = function () {
                const form = proveedorSelect?.closest('form');
                if (!form) {
                    return;
                }

                form.querySelectorAll('input[type="text"], textarea').forEach((field) => {
                    if (field.readOnly) {
                        return;
                    }

                    field.addEventListener('input', function () {
                        this.value = upperText(this.value);
                    });
                });
            };

            function getProveedorOptionByValue(value) {
                if (!proveedorSelect || !value) {
                    return null;
                }

                return Array.from(proveedorSelect.options).find((option) => String(option.value) === String(value)) || null;
            }

            function syncProveedorFields() {
                if (!proveedorSelect) {
                    return;
                }

                const selectedId = String(proveedorSelect.value || '');
                const selectedOption = getProveedorOptionByValue(selectedId);
                const proveedorFromMap = proveedorById.get(selectedId);

                if (!selectedId) {
                    if (provinciaInput) provinciaInput.value = '';
                    if (provinciaIdInput) provinciaIdInput.value = '';
                    if (ciudadInput) ciudadInput.value = '';
                    if (ciudadIdInput) ciudadIdInput.value = '';
                    if (domicilioInput) domicilioInput.value = '';
                    if (telefonoInput) telefonoInput.value = '';
                    if (codigoPostalInput) codigoPostalInput.value = '';
                    if (proveedorDataWarning) {
                        proveedorDataWarning.classList.add('d-none');
                        proveedorDataWarning.textContent = '';
                    }

                    return;
                }

                const proveedor = {
                    provincia_id: proveedorFromMap?.provincia_id || selectedOption?.dataset?.provinciaId || '',
                    provincia_nombre: proveedorFromMap?.provincia_nombre || selectedOption?.dataset?.provinciaNombre || '',
                    ciudad_id: proveedorFromMap?.ciudad_id || selectedOption?.dataset?.ciudadId || '',
                    ciudad_nombre: proveedorFromMap?.ciudad_nombre || selectedOption?.dataset?.ciudadNombre || '',
                    direccion: proveedorFromMap?.direccion || selectedOption?.dataset?.direccion || '',
                    telefono: proveedorFromMap?.telefono || selectedOption?.dataset?.telefono || '',
                    codigo_postal: proveedorFromMap?.codigo_postal || selectedOption?.dataset?.codigoPostal || '',
                };

                if (provinciaInput) provinciaInput.value = upperText(proveedor.provincia_nombre);
                if (provinciaIdInput) provinciaIdInput.value = proveedor.provincia_id ? String(proveedor.provincia_id) : '';
                if (ciudadInput) ciudadInput.value = upperText(proveedor.ciudad_nombre);
                if (ciudadIdInput) ciudadIdInput.value = proveedor.ciudad_id ? String(proveedor.ciudad_id) : '';
                if (domicilioInput) domicilioInput.value = upperText(proveedor.direccion);
                if (telefonoInput) telefonoInput.value = upperText(proveedor.telefono);
                if (codigoPostalInput) codigoPostalInput.value = upperText(proveedor.codigo_postal);

                const missingFields = [];

                if (!proveedor.provincia_nombre) missingFields.push('provincia');
                if (!proveedor.ciudad_nombre) missingFields.push('ciudad');
                if (!proveedor.telefono) missingFields.push('telefono');
                if (!proveedor.direccion) missingFields.push('domicilio');
                if (!proveedor.codigo_postal) missingFields.push('codigo postal');

                if (proveedorDataWarning) {
                    if (missingFields.length > 0) {
                        proveedorDataWarning.textContent = 'Atencion: este proveedor no tiene cargado: ' + missingFields.join(', ') + '.';
                        proveedorDataWarning.classList.remove('d-none');
                    } else {
                        proveedorDataWarning.classList.add('d-none');
                        proveedorDataWarning.textContent = '';
                    }
                }
            }

            async function syncProveedorFieldsFromServer() {
                if (!proveedorSelect) {
                    return;
                }

                const selectedId = String(proveedorSelect.value || '').trim();

                if (!selectedId) {
                    syncProveedorFields();
                    return;
                }

                try {
                    const url = `{{ route('admin.reparaciones-articulos.proveedor.data', ['id' => '__ID__']) }}`.replace('__ID__', encodeURIComponent(selectedId));
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        syncProveedorFields();
                        return;
                    }

                    const proveedor = await response.json();

                    if (provinciaInput) provinciaInput.value = upperText(proveedor.provincia_nombre || '');
                    if (provinciaIdInput) provinciaIdInput.value = proveedor.provincia_id ? String(proveedor.provincia_id) : '';
                    if (ciudadInput) ciudadInput.value = upperText(proveedor.ciudad_nombre || '');
                    if (ciudadIdInput) ciudadIdInput.value = proveedor.ciudad_id ? String(proveedor.ciudad_id) : '';
                    if (domicilioInput) domicilioInput.value = upperText(proveedor.direccion || '');
                    if (telefonoInput) telefonoInput.value = upperText(proveedor.telefono || '');
                    if (codigoPostalInput) codigoPostalInput.value = upperText(proveedor.codigo_postal || '');

                    const missingFields = [];
                    if (!proveedor.provincia_nombre) missingFields.push('provincia');
                    if (!proveedor.ciudad_nombre) missingFields.push('ciudad');
                    if (!proveedor.telefono) missingFields.push('telefono');
                    if (!proveedor.direccion) missingFields.push('domicilio');
                    if (!proveedor.codigo_postal) missingFields.push('codigo postal');

                    if (proveedorDataWarning) {
                        if (missingFields.length > 0) {
                            proveedorDataWarning.textContent = 'Atencion: este proveedor no tiene cargado: ' + missingFields.join(', ') + '.';
                            proveedorDataWarning.classList.remove('d-none');
                        } else {
                            proveedorDataWarning.classList.add('d-none');
                            proveedorDataWarning.textContent = '';
                        }
                    }
                } catch (_error) {
                    syncProveedorFields();
                }
            }

            function setDetalleFeedback(message, type) {
                if (!detalleFeedback) {
                    return;
                }

                const className = type === 'error' ? 'text-danger' : (type === 'ok' ? 'text-success' : 'text-muted');
                detalleFeedback.className = className + ' d-block mb-2';
                detalleFeedback.textContent = message;
            }

            function articuloOptionsHtml() {
                return ['<option value="">Seleccione articulo</option>'].concat(
                    articulos.map((articulo) => `<option value="${articulo.id}">${articulo.label}</option>`)
                ).join('');
            }

            function rowTemplate(index, data) {
                return `
                    <tr data-row>
                        <td>
                            ${data.label}
                            <input type="hidden" name="detalles[${index}][articulo_id]" value="${data.articulo_id || ''}">
                            <input type="hidden" name="detalles[${index}][descripcion_articulo_manual]" value="${data.descripcion_articulo_manual || ''}">
                        </td>
                        <td>
                            ${data.codigo || '-'}
                            <input type="hidden" name="detalles[${index}][codigo_articulo_manual]" value="${data.codigo_articulo_manual || ''}">
                        </td>
                        <td>
                            ${data.cantidad_enviada}
                            <input type="hidden" name="detalles[${index}][cantidad_enviada]" value="${data.cantidad_enviada}">
                        </td>
                        <td>
                            ${data.costo_unitario === '' ? '-' : data.costo_unitario}
                            <input type="hidden" name="detalles[${index}][costo_unitario]" value="${data.costo_unitario}">
                        </td>
                        <td>
                            ${data.observaciones || '-'}
                            <input type="hidden" name="detalles[${index}][observaciones]" value="${data.observaciones || ''}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-light-danger" data-remove-row><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
            }

            function reindexRows() {
                detalleRows.querySelectorAll('[data-row]').forEach((row, index) => {
                    row.querySelectorAll('[name^="detalles["]').forEach((input) => {
                        input.name = input.name.replace(/detalles\[\d+\]/, `detalles[${index}]`);
                    });
                });
            }

            addRowButton?.addEventListener('click', () => {
                const articuloId = detalleArticuloId?.value || '';
                const articuloOption = articuloId && detalleArticuloId ? detalleArticuloId.options[detalleArticuloId.selectedIndex] : null;
                const descripcionManual = upperText((detalleDescripcionManual?.value || '').trim());
                const codigoManual = upperText((detalleCodigoManual?.value || '').trim());
                const cantidad = parseInt(detalleCantidad?.value || '0', 10);
                const costo = (detalleCosto?.value || '').trim();
                const observaciones = upperText((detalleObservaciones?.value || '').trim());

                if (!articuloId && descripcionManual === '') {
                    setDetalleFeedback('Debe seleccionar un articulo de la lista o ingresar un articulo manual.', 'error');
                    return;
                }

                if (!Number.isInteger(cantidad) || cantidad < 1) {
                    setDetalleFeedback('La cantidad debe ser mayor a cero.', 'error');
                    return;
                }

                const index = detalleRows.querySelectorAll('[data-row]').length;
                const label = articuloId ? (articuloOption?.text?.trim() || 'N/A') : descripcionManual;
                const code = articuloId
                    ? ((articuloOption?.text?.split(' - ').slice(1).join(' - ') || '-').trim() || '-')
                    : (codigoManual || '-');

                detalleRows.insertAdjacentHTML('beforeend', rowTemplate(index, {
                    articulo_id: articuloId,
                    descripcion_articulo_manual: articuloId ? '' : descripcionManual,
                    codigo_articulo_manual: articuloId ? '' : codigoManual,
                    cantidad_enviada: cantidad,
                    costo_unitario: costo,
                    observaciones,
                    label,
                    codigo: code,
                }));

                if (detalleArticuloId) {
                    detalleArticuloId.value = '';
                    if (window.jQuery) {
                        window.jQuery(detalleArticuloId).trigger('change');
                    }
                }

                if (detalleDescripcionManual) detalleDescripcionManual.value = '';
                if (detalleCodigoManual) detalleCodigoManual.value = '';
                if (detalleCantidad) detalleCantidad.value = '1';
                if (detalleCosto) detalleCosto.value = '';
                if (detalleObservaciones) detalleObservaciones.value = '';

                setDetalleFeedback('Articulo agregado al detalle.', 'ok');
            });

            detalleRows?.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-row]');
                if (!button) {
                    return;
                }

                button.closest('[data-row]')?.remove();
                reindexRows();
            });

            const form = addRowButton?.closest('form');
            form?.addEventListener('submit', function (event) {
                if (detalleRows.querySelectorAll('[data-row]').length === 0) {
                    event.preventDefault();
                    setDetalleFeedback('Debe agregar al menos un articulo al detalle.', 'error');
                }
            });

            proveedorSelect?.addEventListener('change', syncProveedorFieldsFromServer);
            proveedorSelect?.addEventListener('input', syncProveedorFieldsFromServer);
            proveedorSelect?.addEventListener('blur', syncProveedorFieldsFromServer);
            proveedorSelect?.addEventListener('select2:select', syncProveedorFieldsFromServer);
            proveedorSelect?.addEventListener('select2:clear', syncProveedorFieldsFromServer);

            if (window.jQuery) {
                window.jQuery(proveedorSelect).on('change select2:select select2:clear', syncProveedorFieldsFromServer);
            }

            normalizeUppercaseFormInputs();
            syncProveedorFieldsFromServer();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initReparacionArticuloCreate, { once: true });
        } else {
            initReparacionArticuloCreate();
            }
    </script>
@endpush
