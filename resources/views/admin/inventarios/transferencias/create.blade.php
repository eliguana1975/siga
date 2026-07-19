@extends('layouts.admin')

@php
    $inventariosData = $inventarios->map(function ($inventario) {
        return [
            'id' => $inventario->id,
            'deposito_id' => $inventario->deposito_id,
            'articulo_id' => $inventario->articulo_id,
            'articulo' => $inventario->articulo?->nombre ?? 'N/A',
            'codigo' => $inventario->articulo?->codigo_producto ?? '',
            'unidad' => $inventario->articulo?->unidadMedida?->nombre ?? '',
            'cantidad' => (int) $inventario->cantidad,
        ];
    })->values();
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Nueva transferencia</h3>
                <p class="text-subtitle text-muted">Mueve articulos entre depositos y consulta el stock disponible del origen.</p>
            </div>
            <a href="{{ route('admin.inventarios.transferencias.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <form method="POST" action="{{ route('admin.inventarios.transferencias.store') }}" id="transferenciaForm">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Datos de la transferencia</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="fecha_transferencia" class="form-label">Fecha (*)</label>
                                <input type="date" name="fecha_transferencia" id="fecha_transferencia" class="form-control"
                                    value="{{ old('fecha_transferencia', now()->format('Y-m-d')) }}" required>
                                @error('fecha_transferencia')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="deposito_origen_id" class="form-label">Deposito origen (*)</label>
                                <select name="deposito_origen_id" id="deposito_origen_id" class="form-select" required>
                                    <option value="">Seleccione origen</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected(old('deposito_origen_id') == $deposito->id)>{{ $deposito->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('deposito_origen_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="deposito_destino_id" class="form-label">Deposito destino (*)</label>
                                <select name="deposito_destino_id" id="deposito_destino_id" class="form-select" required>
                                    <option value="">Seleccione destino</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected(old('deposito_destino_id') == $deposito->id)>{{ $deposito->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('deposito_destino_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                                @error('observaciones')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Stock disponible del deposito origen</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="stockSearch" class="form-label">Buscar articulo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="search" id="stockSearch" class="form-control" placeholder="Articulo, codigo o unidad">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Articulo</th>
                                        <th>Codigo</th>
                                        <th>Unidad</th>
                                        <th>Stock</th>
                                        <th class="text-end" style="width: 110px;">Accion</th>
                                    </tr>
                                </thead>
                                <tbody id="stockTableBody"></tbody>
                            </table>
                        </div>
                        <div id="stockEmpty" class="text-center text-muted py-4 d-none">Seleccione un deposito origen con stock disponible.</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Articulos a transferir</h4>
                    </div>
                    <div class="card-body">
                        @error('detalles')<div class="alert alert-danger">{{ $message }}</div>@enderror
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Articulo</th>
                                        <th>Stock origen</th>
                                        <th style="width: 160px;">Cantidad</th>
                                        <th style="width: 90px;">Accion</th>
                                    </tr>
                                </thead>
                                <tbody id="transferTableBody"></tbody>
                            </table>
                        </div>
                        <div id="transferEmpty" class="text-center text-muted py-4">No hay articulos agregados.</div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.inventarios.transferencias.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" id="saveTransferBtn" class="btn btn-success" disabled>
                                <i class="bi bi-check-circle"></i> Confirmar transferencia
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inventarios = @json($inventariosData);

            const origenSelect = document.getElementById('deposito_origen_id');
            const destinoSelect = document.getElementById('deposito_destino_id');
            const stockSearch = document.getElementById('stockSearch');
            const stockTableBody = document.getElementById('stockTableBody');
            const stockEmpty = document.getElementById('stockEmpty');
            const transferTableBody = document.getElementById('transferTableBody');
            const transferEmpty = document.getElementById('transferEmpty');
            const saveTransferBtn = document.getElementById('saveTransferBtn');

            let transferItems = [];

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function stockDisponible() {
                const origenId = String(origenSelect.value || '');
                const term = stockSearch.value.trim().toLowerCase();

                if (!origenId) {
                    return [];
                }

                return inventarios.filter(item => {
                    if (String(item.deposito_id) !== origenId || Number(item.cantidad) <= 0) {
                        return false;
                    }

                    const haystack = [item.articulo, item.codigo, item.unidad].join(' ').toLowerCase();
                    return haystack.includes(term);
                });
            }

            function renderStock() {
                const rows = stockDisponible();
                stockTableBody.innerHTML = '';

                rows.forEach(item => {
                    const alreadyAdded = transferItems.some(transferItem => Number(transferItem.id) === Number(item.id));
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><div class="fw-semibold">${escapeHtml(item.articulo)}</div></td>
                        <td>${escapeHtml(item.codigo || '-')}</td>
                        <td>${escapeHtml(item.unidad || '-')}</td>
                        <td>${item.cantidad}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm ${alreadyAdded ? 'btn-outline-secondary' : 'btn-primary'} stock-add" data-id="${item.id}" ${alreadyAdded ? 'disabled' : ''}>
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </td>
                    `;
                    stockTableBody.appendChild(row);
                });

                const hasOrigen = Boolean(String(origenSelect.value || ''));
                stockEmpty.textContent = hasOrigen
                    ? 'No hay articulos con stock disponible para el deposito origen y la busqueda indicada.'
                    : 'Seleccione un deposito origen con stock disponible.';
                stockEmpty.classList.toggle('d-none', rows.length > 0);
            }

            function renderTransferItems() {
                transferTableBody.innerHTML = '';

                transferItems.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <input type="hidden" name="detalles[${index}][inventario_origen_id]" value="${item.id}">
                            <div class="fw-semibold">${escapeHtml(item.articulo)}</div>
                            <small class="text-muted">${escapeHtml(item.codigo || 'Sin codigo')}${item.unidad ? ' - ' + escapeHtml(item.unidad) : ''}</small>
                        </td>
                        <td>${item.cantidad}</td>
                        <td>
                            <input type="number" name="detalles[${index}][cantidad]" class="form-control form-control-sm transfer-cantidad"
                                data-id="${item.id}" value="${item.transferCantidad}" min="1" max="${item.cantidad}" step="1" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger transfer-remove" data-id="${item.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    transferTableBody.appendChild(row);
                });

                transferEmpty.classList.toggle('d-none', transferItems.length > 0);
                saveTransferBtn.disabled = transferItems.length === 0;
            }

            function resetTransferItems() {
                transferItems = [];
                renderStock();
                renderTransferItems();
            }

            origenSelect.addEventListener('change', resetTransferItems);
            stockSearch.addEventListener('input', renderStock);

            stockTableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.stock-add');

                if (!button) {
                    return;
                }

                const item = inventarios.find(inventario => Number(inventario.id) === Number(button.dataset.id));

                if (!item) {
                    return;
                }

                transferItems.push({ ...item, transferCantidad: 1 });
                renderStock();
                renderTransferItems();
            });

            transferTableBody.addEventListener('input', function (event) {
                const input = event.target.closest('.transfer-cantidad');

                if (!input) {
                    return;
                }

                const item = transferItems.find(transferItem => Number(transferItem.id) === Number(input.dataset.id));

                if (!item) {
                    return;
                }

                const value = Math.max(1, Math.min(Number(input.value || 1), Number(item.cantidad)));
                item.transferCantidad = value;
            });

            transferTableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.transfer-remove');

                if (!button) {
                    return;
                }

                transferItems = transferItems.filter(item => Number(item.id) !== Number(button.dataset.id));
                renderStock();
                renderTransferItems();
            });

            function validarDepositos() {
                if (origenSelect.value && destinoSelect.value && origenSelect.value === destinoSelect.value) {
                    destinoSelect.setCustomValidity('El deposito destino debe ser diferente al origen.');
                } else {
                    destinoSelect.setCustomValidity('');
                }
            }

            destinoSelect.addEventListener('change', validarDepositos);

            if (window.jQuery) {
                window.jQuery(origenSelect).on('select2:select select2:clear change', resetTransferItems);
                window.jQuery(destinoSelect).on('select2:select select2:clear change', validarDepositos);
            }

            renderStock();
            renderTransferItems();
            window.setTimeout(renderStock, 0);
            window.setTimeout(renderStock, 250);
        });
    </script>
@endpush
