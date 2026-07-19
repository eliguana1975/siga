@php
    $editing = isset($entrada);
    $detallesIniciales = old('detalles');

    if ($detallesIniciales === null && $editing) {
        $detallesIniciales = $entrada->detalles->map(fn ($detalle) => [
            'compra_detalle_id' => $detalle->compra_detalle_id,
            'articulo_id' => $detalle->articulo_id,
            'cantidad' => $detalle->cantidad,
            'precio_compra_unidad' => $detalle->precio_compra_unidad,
        ])->values()->all();
    }

    if (empty($detallesIniciales)) {
        $detallesIniciales = [[
            'compra_detalle_id' => '',
            'articulo_id' => '',
            'cantidad' => 1,
            'precio_compra_unidad' => '',
        ]];
    }

    $articleClassifier = app(\App\Services\ArticleClassificationService::class);

    $articuloOptions = $articulos->map(function ($articulo) use ($articleClassifier) {
        return [
            'id' => $articulo->id,
            'label' => $articulo->nombre . ($articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : ''),
            'es_cubierta' => $articleClassifier->isCubiertaArticulo($articulo),
            'cubiertas_existentes' => (int) ($articulo->cubiertas_count ?? 0) > 0,
        ];
    })->values();
@endphp

@push('styles')
    <style>
        .entrada-detalle-table {
            min-width: 1160px;
        }

        .entrada-articulo-cell {
            min-width: 600px;
        }

        .entrada-articulo-inline {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(390px, 1.5fr);
            gap: .75rem;
            align-items: end;
        }

        .cubierta-numbering {
            min-width: 390px;
        }

        .cubierta-numbering-fields {
            display: grid;
            grid-template-columns: minmax(190px, 1fr) minmax(160px, .8fr);
            gap: .5rem;
            align-items: end;
        }
    </style>
@endpush

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="fecha_entrada" class="form-label">Fecha (*)</label>
        <input type="date" name="fecha_entrada" id="fecha_entrada" class="form-control"
            value="{{ old('fecha_entrada', isset($entrada) ? $entrada->fecha_entrada?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('fecha_entrada')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="compra_id" class="form-label">Orden de compra</label>
        <select name="compra_id" id="compra_id" class="form-select">
            <option value="">Sin orden relacionada</option>
            @foreach ($comprasParaIngreso as $compra)
                <option
                    value="{{ $compra['id'] }}"
                    data-deposito-id="{{ $compra['deposito_id'] }}"
                    data-proveedor-id="{{ $compra['proveedor_id'] }}"
                    data-nro-orden="{{ $compra['nro_orden_compra'] }}"
                    data-detalles="{{ base64_encode(json_encode($compra['detalles'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) }}"
                    @selected((string) old('compra_id', $entrada->compra_id ?? '') === (string) $compra['id'])>
                    {{ $compra['label'] }}
                </option>
            @endforeach
        </select>
        @error('compra_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="nro_orden_compra" class="form-label">Nro. orden de compra</label>
        <input type="text" name="nro_orden_compra" id="nro_orden_compra" class="form-control"
            value="{{ old('nro_orden_compra', $entrada->nro_orden_compra ?? '') }}" maxlength="100">
        @error('nro_orden_compra')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="proveedor_id" class="form-label">Proveedor</label>
        <select name="proveedor_id" id="proveedor_id" class="form-select">
            <option value="">Sin proveedor</option>
            @foreach ($proveedores as $proveedor)
                <option value="{{ $proveedor->id }}" @selected((string) old('proveedor_id', $entrada->proveedor_id ?? '') === (string) $proveedor->id)>
                    {{ $proveedor->nombre }}
                </option>
            @endforeach
        </select>
        @error('proveedor_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="nro_comprobante_proveedor" class="form-label">Nro. comprobante proveedor</label>
        <input type="text" name="nro_comprobante_proveedor" id="nro_comprobante_proveedor" class="form-control"
            value="{{ old('nro_comprobante_proveedor', $entrada->nro_comprobante_proveedor ?? '') }}" maxlength="100">
        @error('nro_comprobante_proveedor')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="deposito_id" class="form-label">Deposito (*)</label>
        <select name="deposito_id" id="deposito_id" class="form-select" required>
            <option value="">Seleccione deposito</option>
            @foreach ($depositos as $deposito)
                <option value="{{ $deposito->id }}" @selected((string) old('deposito_id', $entrada->deposito_id ?? '') === (string) $deposito->id)>
                    {{ $deposito->nombre }}
                </option>
            @endforeach
        </select>
        @error('deposito_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-12">
        <label for="observaciones" class="form-label">Observaciones</label>
        <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones', $entrada->observaciones ?? '') }}</textarea>
        @error('observaciones')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Detalle de articulos</h4>
        <button type="button" class="btn btn-sm btn-primary" id="addDetalleRow">
            <i class="bi bi-plus-circle"></i> Agregar articulo
        </button>
    </div>
    <div class="card-body">
        @error('detalles')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="table-responsive">
            <table class="table table-striped mb-0 entrada-detalle-table">
                <thead>
                    <tr>
                        <th>Articulo</th>
                        <th style="width: 130px;">Cantidad</th>
                        <th style="width: 180px;">Precio unidad</th>
                        <th style="width: 80px;">Accion</th>
                    </tr>
                </thead>
                <tbody id="detallesBody">
                    @foreach ($detallesIniciales as $index => $detalle)
                        <tr>
                            <td class="entrada-articulo-cell">
                                <input type="hidden" name="detalles[{{ $index }}][compra_detalle_id]" value="{{ $detalle['compra_detalle_id'] ?? '' }}">
                                @php
                                    $articuloSeleccionado = $articulos->firstWhere('id', $detalle['articulo_id'] ?? null);
                                    $requiereNumeracionCubierta = $articuloSeleccionado
                                        && ((int) ($articuloSeleccionado->cubiertas_count ?? 0) === 0)
                                        && (str_contains(mb_strtolower((string) ($articuloSeleccionado->categoria?->nombre ?? ''), 'UTF-8'), 'cubierta')
                                            || str_contains(mb_strtolower((string) $articuloSeleccionado->nombre, 'UTF-8'), 'cubierta'));
                                @endphp
                                <div class="entrada-articulo-inline">
                                    <div>
                                        @if (!empty($detalle['compra_detalle_id']))
                                            <input type="hidden" name="detalles[{{ $index }}][articulo_id]" value="{{ $detalle['articulo_id'] ?? '' }}">
                                            <div class="form-control-plaintext">
                                                {{ $articuloSeleccionado?->nombre ?? 'Articulo' }}{{ $articuloSeleccionado?->codigo_producto ? ' - ' . $articuloSeleccionado->codigo_producto : '' }}
                                            </div>
                                        @else
                                            <select name="detalles[{{ $index }}][articulo_id]" class="form-select" required>
                                                <option value="">Seleccione articulo</option>
                                                @foreach ($articulos as $articulo)
                                                    <option value="{{ $articulo->id }}" @selected((string) ($detalle['articulo_id'] ?? '') === (string) $articulo->id)>
                                                        {{ $articulo->nombre }}{{ $articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div class="cubierta-numbering {{ $requiereNumeracionCubierta ? '' : 'd-none' }}">
                                        <label class="form-label small mb-1">Primer ingreso: numeracion</label>
                                        <div class="cubierta-numbering-fields">
                                            <select name="detalles[{{ $index }}][cubiertas_tiene_numeracion]" class="form-select form-select-sm cubierta-numbering-mode">
                                                <option value="0" @selected(($detalle['cubiertas_tiene_numeracion'] ?? '0') === '0')>No, iniciar en 001</option>
                                                <option value="1" @selected(($detalle['cubiertas_tiene_numeracion'] ?? '') === '1')>Si, indicar numero inicial</option>
                                            </select>
                                            <input type="number" name="detalles[{{ $index }}][cubiertas_numero_inicial]" class="form-control form-control-sm cubierta-numbering-start"
                                                value="{{ $detalle['cubiertas_numero_inicial'] ?? '' }}" min="1" step="1" placeholder="Nro inicial">
                                        </div>
                                        @error("detalles.{$index}.cubiertas_numero_inicial")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="number" name="detalles[{{ $index }}][cantidad]" class="form-control"
                                    value="{{ $detalle['cantidad'] ?? 1 }}" min="1" step="1" required>
                            </td>
                            <td>
                                <input type="number" name="detalles[{{ $index }}][precio_compra_unidad]" class="form-control"
                                    value="{{ $detalle['precio_compra_unidad'] ?? '' }}" min="0" step="0.01" @readonly(!empty($detalle['compra_detalle_id']))>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-detalle-row">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.getElementById('detallesBody');
            const addButton = document.getElementById('addDetalleRow');
            const compraSelect = document.getElementById('compra_id');
            const depositoSelect = document.getElementById('deposito_id');
            const proveedorSelect = document.getElementById('proveedor_id');
            const nroOrdenInput = document.getElementById('nro_orden_compra');
            let rowIndex = body.querySelectorAll('tr').length;

            const articuloOptions = @json($articuloOptions);

            function optionsHtml() {
                return '<option value="">Seleccione articulo</option>' + articuloOptions
                    .map(articulo => `<option value="${articulo.id}">${String(articulo.label).replace(/</g, '&lt;')}</option>`)
                    .join('');
            }

            function addRow() {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="entrada-articulo-cell">
                        <input type="hidden" name="detalles[${rowIndex}][compra_detalle_id]" value="">
                        <div class="entrada-articulo-inline">
                            <div>
                                <select name="detalles[${rowIndex}][articulo_id]" class="form-select" required>${optionsHtml()}</select>
                            </div>
                            ${cubiertaNumberingHtml(rowIndex)}
                        </div>
                    </td>
                    <td><input type="number" name="detalles[${rowIndex}][cantidad]" class="form-control" value="1" min="1" step="1" required></td>
                    <td><input type="number" name="detalles[${rowIndex}][precio_compra_unidad]" class="form-control" min="0" step="0.01"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-detalle-row"><i class="bi bi-trash"></i></button></td>
                `;
                body.appendChild(row);
                enhanceRow(row);
                rowIndex += 1;
            }

            function addCompraDetalleRow(detalle) {
                const cantidad = Math.max(1, Number(detalle.cantidad_pendiente || 1));
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="entrada-articulo-cell">
                        <input type="hidden" name="detalles[${rowIndex}][compra_detalle_id]" value="${detalle.id}">
                        <input type="hidden" name="detalles[${rowIndex}][articulo_id]" value="${detalle.articulo_id}">
                        <div class="entrada-articulo-inline">
                            <div>
                                <div class="fw-semibold">${escapeHtml(detalle.articulo_label)}</div>
                                <small class="text-muted">Pendiente: ${cantidad}${detalle.unidad ? ' ' + escapeHtml(detalle.unidad) : ''}</small>
                            </div>
                            ${cubiertaNumberingHtml(rowIndex, detalle.es_cubierta && !detalle.cubiertas_existentes)}
                        </div>
                    </td>
                    <td><input type="number" name="detalles[${rowIndex}][cantidad]" class="form-control" value="${cantidad}" min="1" max="${cantidad}" step="1" required></td>
                    <td><input type="number" name="detalles[${rowIndex}][precio_compra_unidad]" class="form-control" value="${Number(detalle.precio_compra_unidad || 0).toFixed(2)}" min="0" step="0.01" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-detalle-row"><i class="bi bi-trash"></i></button></td>
                `;
                body.appendChild(row);
                enhanceRow(row);
                rowIndex += 1;
            }

            function enhanceRow(row) {
                refreshCubiertaNumbering(row);

                if (typeof window.initSigaInputIcons === 'function') {
                    window.initSigaInputIcons(row);
                }
                if (typeof window.initSigaSelect2 === 'function') {
                    window.initSigaSelect2(row);
                }
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function cubiertaNumberingHtml(index, visible = false) {
                return `
                    <div class="cubierta-numbering ${visible ? '' : 'd-none'}">
                        <label class="form-label small mb-1">Primer ingreso: numeracion</label>
                        <div class="cubierta-numbering-fields">
                            <select name="detalles[${index}][cubiertas_tiene_numeracion]" class="form-select form-select-sm cubierta-numbering-mode">
                                <option value="0">No, iniciar en 001</option>
                                <option value="1">Si, indicar numero inicial</option>
                            </select>
                            <input type="number" name="detalles[${index}][cubiertas_numero_inicial]" class="form-control form-control-sm cubierta-numbering-start" min="1" step="1" placeholder="Nro inicial">
                        </div>
                    </div>
                `;
            }

            function refreshCubiertaNumbering(row) {
                const articuloInput = row.querySelector('[name$="[articulo_id]"]');
                const block = row.querySelector('.cubierta-numbering');

                if (!articuloInput || !block) {
                    return;
                }

                const articulo = articuloOptions.find(item => String(item.id) === String(articuloInput.value));
                const visible = Boolean(articulo?.es_cubierta && !articulo?.cubiertas_existentes);
                block.classList.toggle('d-none', !visible);

                if (!visible) {
                    const mode = block.querySelector('.cubierta-numbering-mode');
                    const start = block.querySelector('.cubierta-numbering-start');

                    if (mode) {
                        mode.value = '0';
                    }

                    if (start) {
                        start.value = '';
                    }
                }
            }

            function setSelectValue(select, value) {
                select.value = value ? String(value) : '';
                select.dispatchEvent(new Event('change', { bubbles: true }));

                if (window.jQuery && window.jQuery.fn?.select2) {
                    window.jQuery(select).trigger('change.select2');
                }
            }

            function getSelectedCompra() {
                const option = compraSelect.selectedOptions[0];

                if (!option || !option.value) {
                    return null;
                }

                return {
                    id: option.value,
                    deposito_id: option.dataset.depositoId || '',
                    proveedor_id: option.dataset.proveedorId || '',
                    nro_orden_compra: option.dataset.nroOrden || option.value,
                    detalles: JSON.parse(window.atob(option.dataset.detalles || 'W10=')),
                };
            }

            function loadSelectedCompra() {
                const compra = getSelectedCompra();

                if (!compra) {
                    return;
                }

                setSelectValue(depositoSelect, compra.deposito_id);
                setSelectValue(proveedorSelect, compra.proveedor_id);
                nroOrdenInput.value = compra.nro_orden_compra;
                body.innerHTML = '';
                rowIndex = 0;
                compra.detalles.forEach(addCompraDetalleRow);
            }

            addButton.addEventListener('click', addRow);
            compraSelect.addEventListener('change', loadSelectedCompra);

            if (window.jQuery) {
                window.jQuery(compraSelect).on('select2:select', loadSelectedCompra);
            }

            if (compraSelect.value && body.querySelectorAll('tr').length === 1 && !body.querySelector('input[name$="[compra_detalle_id]"]')?.value) {
                loadSelectedCompra();
            }

            body.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-detalle-row');
                if (!button) {
                    return;
                }

                if (body.querySelectorAll('tr').length === 1) {
                    return;
                }

                button.closest('tr').remove();
            });

            body.addEventListener('change', function (event) {
                if (event.target.matches('[name$="[articulo_id]"]')) {
                    refreshCubiertaNumbering(event.target.closest('tr'));
                }
            });

            body.querySelectorAll('tr').forEach(refreshCubiertaNumbering);
        });
    </script>
@endpush
