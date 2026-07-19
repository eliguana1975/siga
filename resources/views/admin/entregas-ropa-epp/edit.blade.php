@extends('layouts.admin')

@php
    $ropaEppOptions = $ropaEpp->map(fn ($inventario) => [
        'id' => $inventario->articulo_id,
        'label' => trim(($inventario->articulo?->nombre ?? 'prenda/EPP') . ($inventario->articulo?->codigo_producto ? ' - ' . $inventario->articulo->codigo_producto : '')),
        'codigo' => $inventario->articulo?->codigo_producto ?? '',
        'categoria' => $inventario->articulo?->categoria?->nombre ?? '',
        'esRopaEpp' => (bool) ($inventario->articulo?->es_ropa_epp ?? false),
        'stock' => (int) ($inventario->stock_para_entrega ?? $inventario->cantidad),
        'unidad' => $inventario->articulo?->unidadMedida?->nombre ?? '',
    ])->values();

    $initialRows = collect(old('detalles', $entrega->detalles->map(fn ($detalle) => [
        'articulo_id' => $detalle->articulo_id,
        'cantidad' => $detalle->cantidad_entregada,
        'condicion_entrega' => $detalle->condicion_entrega,
    ])->values()->all()))->values();
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar entrega de ropa y EPP #{{ $entrega->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza los datos y la ropa/EPP entregados al empleado.</p>
            </div>
            <a href="{{ route('admin.entregas-ropa-epp.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.entregas-ropa-epp.update', $entrega->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Empleado (*)</label>
                                <select id="empleado_id" name="empleado_id" class="form-select" required>
                                    <option value="">Seleccione empleado</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id }}" @selected((string) old('empleado_id', request('empleado_id', $entrega->empleado_id)) === (string) $empleado->id)>
                                            {{ trim($empleado->apellidos . ' ' . $empleado->nombres) }} - {{ $empleado->numero_doc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Deposito (*)</label>
                                <select id="deposito_id" name="deposito_id" class="form-select" required data-ppes-url="{{ route('admin.entregas-ropa-epp.edit', $entrega->id) }}">
                                    <option value="">Seleccione deposito</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected((string) old('deposito_id', $selectedDepositoId) === (string) $deposito->id)>
                                            {{ $deposito->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha entrega (*)</label>
                                <input id="fecha_entrega" type="date" name="fecha_entrega" class="form-control" value="{{ old('fecha_entrega', request('fecha_entrega', $entrega->fecha_entrega?->format('Y-m-d'))) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea id="observaciones" name="observaciones" class="form-control" rows="2">{{ old('observaciones', request('observaciones', $entrega->observaciones)) }}</textarea>
                            </div>
                        </div>

                        <div class="border rounded p-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0">Detalle de ropa y EPP</h5>
                            </div>
                            <div class="row g-2 align-items-end mb-1">
                                <div class="col-12 col-lg-4">
                                    <label for="ppe-codigo-scan" class="form-label">Codigo / barras</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                        <input type="text" id="ppe-codigo-scan" class="form-control"
                                            placeholder="Escanee o escriba codigo" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label for="ppe-articulo-select" class="form-label">Articulo (*)</label>
                                    <select id="ppe-articulo-select" class="form-select form-select-sm js-select2" data-placeholder="Escriba para buscar articulo">
                                        <option value="">Seleccione prenda/EPP</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 col-lg-2">
                                    <label for="ppe-cantidad-input" class="form-label">Cantidad (*)</label>
                                    <input type="number" id="ppe-cantidad-input" class="form-control form-control-sm" min="1" value="1">
                                </div>
                                <div class="col-12 col-md-3 col-lg-2">
                                    <button type="button" id="addppeRow" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Agregar
                                    </button>
                                </div>
                            </div>
                            <small id="ppe-codigo-feedback" class="text-muted d-block mb-3">
                                Preparado para lector: el escaneo carga la prenda/EPP por codigo.
                            </small>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>prenda/EPP</th>
                                            <th style="width: 130px;">Cantidad</th>
                                            <th>Condicion</th>
                                            <th style="width: 55px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="ppeRows"></tbody>
                                </table>
                            </div>
                            @if ($ropaEpp->isEmpty())
                                <p class="text-muted mb-0">No hay ropa o EPP con stock disponible para el deposito seleccionado.</p>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.entregas-ropa-epp.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar entrega</button>
                        </div>
                    </form>
                    <script type="application/json" id="ppe-options-data">@json($ropaEppOptions)</script>
                    <script type="application/json" id="ppe-initial-rows-data">@json($initialRows)</script>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.getElementById('ppeRows');
            const addButton = document.getElementById('addppeRow');
            const articuloSelect = document.getElementById('ppe-articulo-select');
            const cantidadInput = document.getElementById('ppe-cantidad-input');
            const empleadoSelect = document.getElementById('empleado_id');
            const depositoSelect = document.getElementById('deposito_id');
            const fechaEntregaInput = document.getElementById('fecha_entrega');
            const observacionesInput = document.getElementById('observaciones');
            const codigoInput = document.getElementById('ppe-codigo-scan');
            const feedback = document.getElementById('ppe-codigo-feedback');
            const options = JSON.parse(document.getElementById('ppe-options-data')?.textContent || '[]');
            const initialRows = JSON.parse(document.getElementById('ppe-initial-rows-data')?.textContent || '[]');

            function escapeHtml(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function optionHtml(selected) {
                return ['<option value="">Seleccione prenda/EPP</option>'].concat(options.map(option => {
                    const isSelected = String(selected ?? '') === String(option.id) ? 'selected' : '';
                    return `<option value="${escapeHtml(option.id)}" ${isSelected}>${escapeHtml(option.label)} - Stock ${escapeHtml(option.stock)} ${escapeHtml(option.unidad)}</option>`;
                })).join('');
            }

            function refreshArticuloOptions() {
                if (!articuloSelect) {
                    return;
                }

                articuloSelect.innerHTML = optionHtml();

                if (window.jQuery) {
                    window.jQuery(articuloSelect).trigger('change.select2');
                }
            }

            function setFeedback(message, className) {
                if (!feedback) {
                    return;
                }

                feedback.className = className + ' d-block mt-1';
                feedback.textContent = message;
            }

            function findOptionByCode(code) {
                const normalizedCode = String(code ?? '').trim().toLowerCase();

                if (!normalizedCode) {
                    return null;
                }

                const isRopaEppCategoria = function (categoria) {
                    const normalized = String(categoria ?? '').trim().toLowerCase();
                    return normalized.includes('ropa') || normalized.includes('epp') || normalized.includes('indument') || normalized.includes('protec');
                };

                const matches = options.filter(option => String(option.codigo ?? '').trim().toLowerCase() === normalizedCode);

                if (matches.length === 0) {
                    return null;
                }

                const byVista = matches.filter(option => option.esRopaEpp);
                const byCategoria = byVista.filter(option => isRopaEppCategoria(option.categoria));
                const candidates = byCategoria.length > 0 ? byCategoria : byVista;

                return {
                    option: candidates[0] || null,
                    totalMatches: matches.length,
                    usedCategoryFilter: matches.length > 1 && byCategoria.length > 0,
                };
            }

            function rowTemplate(index, data = {}) {
                return `
                    <tr data-ppe-row>
                        <td>
                            <span class="fw-semibold" data-row-label></span>
                            <input type="hidden" name="detalles[${index}][articulo_id]" value="">
                        </td>
                        <td><input type="number" name="detalles[${index}][cantidad]" class="form-control form-control-sm" min="1" value="${escapeHtml(data.cantidad || 1)}" required></td>
                        <td><input type="text" name="detalles[${index}][condicion_entrega]" class="form-control form-control-sm" value="${escapeHtml(data.condicion_entrega || '')}" placeholder="Buena / usada"></td>
                        <td><button type="button" class="btn btn-sm btn-light-danger" data-remove-row><i class="bi bi-trash"></i></button></td>
                    </tr>
                `;
            }

            function reindex() {
                rows.querySelectorAll('[data-ppe-row]').forEach((row, index) => {
                    row.querySelectorAll('[name^="detalles["]').forEach(input => {
                        input.name = input.name.replace(/detalles\[\d+\]/, `detalles[${index}]`);
                    });
                });
            }

            function appendRow(data = {}) {
                const index = rows.querySelectorAll('[data-ppe-row]').length;
                rows.insertAdjacentHTML('beforeend', rowTemplate(index, data));

                const row = rows.querySelectorAll('[data-ppe-row]')[index];

                if (!row) {
                    return null;
                }

                const articuloIdInput = row.querySelector('input[name$="[articulo_id]"]');
                const rowLabel = row.querySelector('[data-row-label]');

                if (articuloIdInput && data.articulo_id) {
                    articuloIdInput.value = String(data.articulo_id);
                }

                if (rowLabel) {
                    const option = options.find(option => String(option.id) === String(data.articulo_id));
                    rowLabel.textContent = option ? option.label : 'Prenda/EPP';
                }

                return row;
            }

            function selectByCode() {
                const match = findOptionByCode(codigoInput?.value);
                const option = match?.option;

                if (!option) {
                    setFeedback('El codigo escaneado no corresponde a una prenda/EPP disponible.', 'text-warning');
                    return false;
                }

                if (!articuloSelect) {
                    setFeedback('No se pudo preparar el selector de articulos.', 'text-danger');
                    return false;
                }

                articuloSelect.value = String(option.id);
                if (window.jQuery) {
                    window.jQuery(articuloSelect).trigger('change');
                } else {
                    articuloSelect.dispatchEvent(new Event('change'));
                }
                cantidadInput?.focus();
                cantidadInput?.select();

                const selectedMessage = (match.totalMatches > 1 && match.usedCategoryFilter)
                    ? 'Prenda/EPP seleccionada por categoria: ' + option.label
                    : 'Prenda/EPP seleccionada: ' + option.label;

                setFeedback(selectedMessage, 'text-success');
                return true;
            }

            function resetEntryControls() {
                if (codigoInput) {
                    codigoInput.value = '';
                }

                if (articuloSelect) {
                    articuloSelect.value = '';

                    if (window.jQuery) {
                        window.jQuery(articuloSelect).trigger('change');
                    } else {
                        articuloSelect.dispatchEvent(new Event('change'));
                    }
                }

                if (cantidadInput) {
                    cantidadInput.value = '1';
                }

                setFeedback('Preparado para lector: el escaneo carga la prenda/EPP por codigo.', 'text-muted');
            }

            function addSelectedArticulo() {
                const articuloId = articuloSelect?.value;
                const cantidad = parseInt(cantidadInput?.value || '0', 10);

                if (!articuloId) {
                    setFeedback('Seleccione una prenda/EPP antes de agregar.', 'text-warning');
                    articuloSelect?.focus();
                    return false;
                }

                if (!cantidad || cantidad < 1) {
                    setFeedback('Ingrese una cantidad valida mayor a cero.', 'text-warning');
                    cantidadInput?.focus();
                    return false;
                }

                const option = options.find(option => option.esRopaEpp && String(option.id) === String(articuloId));

                if (!option) {
                    setFeedback('El articulo seleccionado no corresponde a ropa/EPP.', 'text-warning');
                    return false;
                }

                const row = appendRow({ articulo_id: articuloId, cantidad: cantidad });

                if (!row) {
                    setFeedback('No se pudo agregar la prenda/EPP al detalle.', 'text-danger');
                    return false;
                }

                resetEntryControls();
                codigoInput?.focus();

                return true;
            }

            addButton?.addEventListener('click', function () {
                addSelectedArticulo();
            });

            rows?.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-row]');
                if (!button) return;
                button.closest('[data-ppe-row]')?.remove();
                reindex();
            });

            depositoSelect?.addEventListener('change', function () {
                const url = new URL(this.dataset.ppesUrl, window.location.origin);

                if (this.value) {
                    url.searchParams.set('deposito_id', this.value);
                }

                if (empleadoSelect?.value) {
                    url.searchParams.set('empleado_id', empleadoSelect.value);
                }

                if (fechaEntregaInput?.value) {
                    url.searchParams.set('fecha_entrega', fechaEntregaInput.value);
                }

                if (observacionesInput?.value.trim()) {
                    url.searchParams.set('observaciones', observacionesInput.value.trim());
                }

                window.location.href = url.toString();
            });

            codigoInput?.addEventListener('input', function () {
                if (codigoInput.value.trim().length >= 3) {
                    selectByCode();
                } else {
                    setFeedback('Preparado para lector: el escaneo carga la prenda/EPP por codigo.', 'text-muted');
                }
            });

            codigoInput?.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                if (selectByCode()) {
                    addSelectedArticulo();
                }
            });

            cantidadInput?.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                addSelectedArticulo();
            });

            refreshArticuloOptions();

            if (initialRows.length > 0) {
                initialRows.forEach((row) => appendRow(row));
            }

            if (codigoInput) {
                codigoInput.focus();
            }
        });
    </script>
@endpush

