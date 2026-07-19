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
        'codigo' => $articulo->codigo_producto,
    ])->values();

    $detallesBase = $reparacion->detalles->map(fn ($detalle) => [
        'id' => $detalle->id,
        'articulo_id' => $detalle->articulo_id,
        'descripcion_articulo_manual' => $detalle->descripcion_articulo_manual,
        'codigo_articulo_manual' => $detalle->codigo_articulo_manual,
        'cantidad_enviada' => $detalle->cantidad_enviada,
        'cantidad_devuelta' => $detalle->cantidad_devuelta,
        'costo_unitario' => $detalle->costo_unitario,
        'observaciones' => $detalle->observaciones,
    ])->values()->all();

    $cantidadDevueltaById = $reparacion->detalles->mapWithKeys(fn ($detalle) => [$detalle->id => (int) $detalle->cantidad_devuelta]);

    $detallesIniciales = collect(old('detalles', $detallesBase))
        ->map(function ($detalle) use ($cantidadDevueltaById) {
            if (! is_array($detalle)) {
                return null;
            }

            $detalleId = isset($detalle['id']) && $detalle['id'] !== '' ? (int) $detalle['id'] : null;
            $cantidadDevuelta = isset($detalle['cantidad_devuelta']) && $detalle['cantidad_devuelta'] !== ''
                ? (int) $detalle['cantidad_devuelta']
                : (int) ($detalleId ? ($cantidadDevueltaById[$detalleId] ?? 0) : 0);

            return [
                'id' => $detalleId,
                'articulo_id' => isset($detalle['articulo_id']) && $detalle['articulo_id'] !== '' ? (int) $detalle['articulo_id'] : null,
                'descripcion_articulo_manual' => $detalle['descripcion_articulo_manual'] ?? null,
                'codigo_articulo_manual' => $detalle['codigo_articulo_manual'] ?? null,
                'cantidad_enviada' => isset($detalle['cantidad_enviada']) ? (int) $detalle['cantidad_enviada'] : 1,
                'cantidad_devuelta' => $cantidadDevuelta,
                'costo_unitario' => $detalle['costo_unitario'] ?? null,
                'observaciones' => $detalle['observaciones'] ?? null,
            ];
        })
        ->filter()
        ->values();
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar reparacion {{ $reparacion->numero_orden }}</h3>
                <p class="text-subtitle text-muted">Actualiza proveedor, fechas y detalle de articulos enviados.</p>
            </div>
            <a href="{{ route('admin.reparaciones-articulos.show', $reparacion->id) }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reparaciones-articulos.update', $reparacion->id) }}">
                        @csrf
                        @method('PUT')

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
                                        <div class="col-12">
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
                                            @selected((string) old('proveedor_id', $reparacion->proveedor_id) === (string) $proveedor->id)>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha envio (*)</label>
                                <input type="date" name="fecha_envio" class="form-control" value="{{ old('fecha_envio', optional($reparacion->fecha_envio)->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha compromiso</label>
                                <input type="date" name="fecha_compromiso" class="form-control" value="{{ old('fecha_compromiso', optional($reparacion->fecha_compromiso)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Provincia proveedor</label>
                                <input id="provincia_nombre" type="text" class="form-control" value="{{ old('provincia_nombre', $reparacion->provincia?->nombre) }}" readonly>
                                <input id="provincia_id" type="hidden" name="provincia_id" value="{{ old('provincia_id', $reparacion->provincia_id) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Ciudad proveedor</label>
                                <input id="ciudad_nombre" type="text" class="form-control" value="{{ old('ciudad_nombre', $reparacion->ciudad?->nombre) }}" readonly>
                                <input id="ciudad_id" type="hidden" name="ciudad_id" value="{{ old('ciudad_id', $reparacion->ciudad_id) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Telefono proveedor</label>
                                <input id="telefono" type="text" name="telefono" class="form-control" value="{{ old('telefono', $reparacion->telefono) }}" maxlength="50" readonly>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label">Domicilio proveedor</label>
                                <input id="domicilio" type="text" name="domicilio" class="form-control" value="{{ old('domicilio', $reparacion->domicilio) }}" maxlength="255" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Codigo postal proveedor</label>
                                <input id="codigo_postal" type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $reparacion->codigo_postal) }}" maxlength="20" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observaciones generales</label>
                                <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $reparacion->observaciones) }}</textarea>
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
                                Puedes agregar, quitar y ajustar cantidades. Si una linea tiene devoluciones, no podras eliminarla ni bajar de lo ya devuelto.
                            </small>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Articulo</th>
                                            <th style="width: 150px;">Codigo</th>
                                            <th style="width: 170px;">Cant. enviada</th>
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
                            <a href="{{ route('admin.reparaciones-articulos.show', $reparacion->id) }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script type="application/json" id="reparacion-proveedores-data">@json($proveedorOptions)</script>
    <script type="application/json" id="reparacion-articulos-data">@json($articuloOptions)</script>
    <script type="application/json" id="reparacion-detalles-data">@json($detallesIniciales)</script>
@endsection

@push('scripts')
    <script>
        const initReparacionArticuloEdit = function () {
            const proveedores = JSON.parse(document.getElementById('reparacion-proveedores-data')?.textContent || '[]');
            const articulos = JSON.parse(document.getElementById('reparacion-articulos-data')?.textContent || '[]');
            const detallesIniciales = JSON.parse(document.getElementById('reparacion-detalles-data')?.textContent || '[]');

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

            if (!proveedorSelect || !detalleRows) {
                return;
            }

            const proveedorById = new Map(proveedores.map((proveedor) => [String(proveedor.id), proveedor]));
            const articuloById = new Map(articulos.map((articulo) => [String(articulo.id), articulo]));

            const upperText = function (value) {
                return String(value || '').toLocaleUpperCase('es-AR');
            };

            const escapeHtml = function (value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };

            const normalizeUppercaseFormInputs = function () {
                const form = proveedorSelect.closest('form');
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

            function syncProveedorFields() {
                const selectedId = String(proveedorSelect.value || '');
                const option = proveedorSelect.options[proveedorSelect.selectedIndex];
                const proveedor = selectedId ? proveedorById.get(selectedId) : null;

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

                const resolved = {
                    provincia_id: proveedor?.provincia_id || option?.dataset?.provinciaId || '',
                    provincia_nombre: proveedor?.provincia_nombre || option?.dataset?.provinciaNombre || '',
                    ciudad_id: proveedor?.ciudad_id || option?.dataset?.ciudadId || '',
                    ciudad_nombre: proveedor?.ciudad_nombre || option?.dataset?.ciudadNombre || '',
                    direccion: proveedor?.direccion || option?.dataset?.direccion || '',
                    telefono: proveedor?.telefono || option?.dataset?.telefono || '',
                    codigo_postal: proveedor?.codigo_postal || option?.dataset?.codigoPostal || '',
                };

                if (provinciaInput) provinciaInput.value = upperText(resolved.provincia_nombre);
                if (provinciaIdInput) provinciaIdInput.value = resolved.provincia_id ? String(resolved.provincia_id) : '';
                if (ciudadInput) ciudadInput.value = upperText(resolved.ciudad_nombre);
                if (ciudadIdInput) ciudadIdInput.value = resolved.ciudad_id ? String(resolved.ciudad_id) : '';
                if (domicilioInput) domicilioInput.value = upperText(resolved.direccion);
                if (telefonoInput) telefonoInput.value = upperText(resolved.telefono);
                if (codigoPostalInput) codigoPostalInput.value = upperText(resolved.codigo_postal);

                const missingFields = [];
                if (!resolved.provincia_nombre) missingFields.push('provincia');
                if (!resolved.ciudad_nombre) missingFields.push('ciudad');
                if (!resolved.telefono) missingFields.push('telefono');
                if (!resolved.direccion) missingFields.push('domicilio');
                if (!resolved.codigo_postal) missingFields.push('codigo postal');

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

            function setDetalleFeedback(message, type) {
                if (!detalleFeedback) {
                    return;
                }

                const className = type === 'error' ? 'text-danger' : (type === 'ok' ? 'text-success' : 'text-muted');
                detalleFeedback.className = className + ' d-block mb-2';
                detalleFeedback.textContent = message;
            }

            function getRowLabel(data) {
                if (data.articulo_id) {
                    return articuloById.get(String(data.articulo_id))?.label || 'ARTICULO';
                }

                return data.descripcion_articulo_manual || 'ARTICULO MANUAL';
            }

            function getRowCode(data) {
                if (data.articulo_id) {
                    return articuloById.get(String(data.articulo_id))?.codigo || '-';
                }

                return data.codigo_articulo_manual || '-';
            }

            function rowTemplate(index, data) {
                const cantidadDevuelta = Math.max(0, parseInt(data.cantidad_devuelta || 0, 10));
                const minCantidad = Math.max(1, cantidadDevuelta);
                const canDelete = cantidadDevuelta === 0;
                const rowLabel = escapeHtml(getRowLabel(data));
                const rowCode = escapeHtml(getRowCode(data));

                return `
                    <tr data-row>
                        <td>
                            ${rowLabel}
                            <input type="hidden" name="detalles[${index}][id]" value="${escapeHtml(data.id || '')}">
                            <input type="hidden" name="detalles[${index}][articulo_id]" value="${escapeHtml(data.articulo_id || '')}">
                            <input type="hidden" name="detalles[${index}][descripcion_articulo_manual]" value="${escapeHtml(data.descripcion_articulo_manual || '')}">
                            <input type="hidden" name="detalles[${index}][codigo_articulo_manual]" value="${escapeHtml(data.codigo_articulo_manual || '')}">
                            ${cantidadDevuelta > 0 ? `<small class="d-block text-muted">Devueltas: ${cantidadDevuelta}</small>` : ''}
                        </td>
                        <td>${rowCode}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm" min="${minCantidad}" name="detalles[${index}][cantidad_enviada]" value="${escapeHtml(data.cantidad_enviada || 1)}" required>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm" min="0" step="0.01" name="detalles[${index}][costo_unitario]" value="${escapeHtml(data.costo_unitario || '')}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm js-uppercase" maxlength="255" name="detalles[${index}][observaciones]" value="${escapeHtml(data.observaciones || '')}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-light-danger" data-remove-row ${canDelete ? '' : 'disabled'} title="${canDelete ? 'Quitar linea' : 'No se puede eliminar: tiene devoluciones'}"><i class="bi bi-trash"></i></button>
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

            function renderRows(rows) {
                detalleRows.innerHTML = '';
                rows.forEach((row, index) => {
                    detalleRows.insertAdjacentHTML('beforeend', rowTemplate(index, row));
                });
            }

            function addRowFromInputs() {
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

                const currentRows = detalleRows.querySelectorAll('[data-row]').length;
                const optionText = articuloOption?.text?.trim() || '';
                const codeFromOption = optionText.includes(' - ') ? optionText.split(' - ').slice(1).join(' - ').trim() : '';

                detalleRows.insertAdjacentHTML('beforeend', rowTemplate(currentRows, {
                    id: '',
                    articulo_id: articuloId ? parseInt(articuloId, 10) : null,
                    descripcion_articulo_manual: articuloId ? '' : descripcionManual,
                    codigo_articulo_manual: articuloId ? '' : codigoManual,
                    cantidad_enviada: cantidad,
                    cantidad_devuelta: 0,
                    costo_unitario: costo,
                    observaciones,
                    label: articuloId ? optionText : descripcionManual,
                    codigo: articuloId ? (codeFromOption || '-') : (codigoManual || '-'),
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

                reindexRows();
                setDetalleFeedback('Articulo agregado al detalle.', 'ok');
            }

            addRowButton?.addEventListener('click', addRowFromInputs);

            detalleRows.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-row]');
                if (!button) {
                    return;
                }

                if (button.disabled) {
                    return;
                }

                button.closest('[data-row]')?.remove();
                reindexRows();
            });

            const form = proveedorSelect.closest('form');
            form?.addEventListener('submit', function (event) {
                if (detalleRows.querySelectorAll('[data-row]').length === 0) {
                    event.preventDefault();
                    setDetalleFeedback('Debe agregar al menos un articulo al detalle.', 'error');
                    return;
                }

                form.querySelectorAll('.js-uppercase').forEach((field) => {
                    field.value = upperText(field.value);
                });
            });

            proveedorSelect.addEventListener('change', syncProveedorFields);
            if (window.jQuery) {
                window.jQuery(proveedorSelect).on('change select2:select select2:clear', syncProveedorFields);
            }

            normalizeUppercaseFormInputs();
            renderRows(detallesIniciales);
            syncProveedorFields();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initReparacionArticuloEdit, { once: true });
        } else {
            initReparacionArticuloEdit();
        }
    </script>
@endpush
