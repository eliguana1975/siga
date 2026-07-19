@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Nuevo pedido de articulos</h3>
                <p class="text-subtitle text-muted">Carga articulos y cantidades para preparar el pedido.</p>
            </div>
            <a href="{{ route('admin.pedidos-articulos.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Datos del pedido</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="deposito_id" class="form-label">Deposito (*)</label>
                            <select id="deposito_id" class="form-select" required>
                                <option value="">Seleccione deposito</option>
                                @foreach ($depositos as $deposito)
                                    <option value="{{ $deposito->id }}">{{ $deposito->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="notas" class="form-label">Notas</label>
                            <input type="text" id="notas" name="notas" form="storePedidoForm" class="form-control"
                                placeholder="Observaciones del pedido">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Articulos</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="articuloSearch" class="form-label">Buscar articulo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="articuloSearch" class="form-control" placeholder="Nombre, codigo o unidad">
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
                                    <th>Punto pedido</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="articulosTableBody"></tbody>
                        </table>
                    </div>

                    <div id="emptyArticulos" class="text-center text-muted py-4 d-none">No hay articulos para mostrar.</div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                        <small id="articulosPaginationInfo" class="text-muted"></small>
                        <div id="articulosPagination" class="btn-group btn-group-sm" role="group" aria-label="Paginacion de articulos"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Detalle de articulos</h4>
                    <button type="button" id="clearItemsBtn" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i> Limpiar
                    </button>
                </div>
                <div class="card-body">
                    <div id="itemsAlert" class="alert d-none" role="alert"></div>

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th style="width: 170px;">Cantidad</th>
                                    <th style="width: 90px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody"></tbody>
                        </table>
                    </div>

                    <div id="emptyItems" class="text-center text-muted py-4 d-none">No hay articulos cargados.</div>

                    <form id="storePedidoForm" method="POST" action="{{ route('admin.pedidos-articulos.store') }}" class="d-flex justify-content-end gap-2 mt-3">
                        @csrf
                        <a href="{{ route('admin.pedidos-articulos.index') }}" class="btn btn-light-secondary">Cancelar</a>
                        <button type="submit" id="storePedidoBtn" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Guardar pedido
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <div id="pedidoConfig"
        data-add-url="{{ route('admin.pedidos-articulos.additem') }}"
        data-update-url-template="{{ route('admin.pedidos-articulos.updateitem', ['itemId' => '__ITEM_ID__']) }}"
        data-remove-url-template="{{ route('admin.pedidos-articulos.removeitem', ['itemId' => '__ITEM_ID__']) }}"
        data-clear-url="{{ route('admin.pedidos-articulos.clearitems') }}">
    </div>
@endsection

@php
    $pedidoArticulosCreateData = [
        'articulos' => $articulos,
        'stockPorDeposito' => $stockPorDeposito,
        'canBypassStockBlock' => $canBypassStockBlock,
        'items' => $items,
    ];
@endphp

<script type="application/json" id="pedido-articulos-create-data">@json($pedidoArticulosCreateData)</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = document.getElementById('pedidoConfig');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const dataElement = document.getElementById('pedido-articulos-create-data');
            let initialData = {
                articulos: [],
                stockPorDeposito: {},
                canBypassStockBlock: false,
                items: [],
            };

            try {
                initialData = JSON.parse(dataElement?.textContent || '{}');
            } catch (error) {
                initialData = {
                    articulos: [],
                    stockPorDeposito: {},
                    canBypassStockBlock: false,
                    items: [],
                };
            }

            const articulos = Array.isArray(initialData.articulos) ? initialData.articulos : [];
            const stockPorDeposito = initialData.stockPorDeposito || {};
            const canBypassStockBlock = Boolean(initialData.canBypassStockBlock);
            let items = Array.isArray(initialData.items) ? initialData.items : [];

            const addUrl = config.dataset.addUrl;
            const updateUrlTemplate = config.dataset.updateUrlTemplate;
            const removeUrlTemplate = config.dataset.removeUrlTemplate;
            const clearUrl = config.dataset.clearUrl;

            const depositoSelect = document.getElementById('deposito_id');
            const articuloSearch = document.getElementById('articuloSearch');
            const articulosTableBody = document.getElementById('articulosTableBody');
            const emptyArticulos = document.getElementById('emptyArticulos');
            const articulosPagination = document.getElementById('articulosPagination');
            const articulosPaginationInfo = document.getElementById('articulosPaginationInfo');
            const tableBody = document.getElementById('itemsTableBody');
            const emptyItems = document.getElementById('emptyItems');
            const itemsAlert = document.getElementById('itemsAlert');
            const storePedidoBtn = document.getElementById('storePedidoBtn');
            const clearItemsBtn = document.getElementById('clearItemsBtn');

            const articulosPerPage = 8;
            let articulosPage = 1;

            function escapeHtml(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function requestJson(url, method, payload = {}) {
                return fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: method === 'GET' ? undefined : JSON.stringify(payload),
                }).then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                });
            }

            function showAlert(message, type = 'success') {
                itemsAlert.className = 'alert alert-' + type;
                itemsAlert.textContent = message;
                itemsAlert.classList.remove('d-none');
                window.setTimeout(() => itemsAlert.classList.add('d-none'), 3000);
            }

            function getArticuloUnidad(articulo) {
                return articulo?.unidad_medida?.nombre || articulo?.unidadMedida?.nombre || '';
            }

            function getItemArticulo(item) {
                return item.articulo || {};
            }

            function getStockActual(articuloId, depositoId) {
                return Number(stockPorDeposito[String(depositoId)]?.[String(articuloId)] || 0);
            }

            function getPuntoPedido(articulo) {
                return Number(articulo?.stock_pedido || 0);
            }

            function isArticuloBloqueado(articulo) {
                const depositoId = depositoSelect.value;
                const puntoPedido = getPuntoPedido(articulo);

                if (canBypassStockBlock) {
                    return false;
                }

                return depositoId && puntoPedido > 0 && getStockActual(articulo.id, depositoId) > puntoPedido;
            }

            function isArticuloSobrePuntoPedido(articulo) {
                const depositoId = depositoSelect.value;
                const puntoPedido = getPuntoPedido(articulo);

                return Boolean(depositoId && puntoPedido > 0 && getStockActual(articulo.id, depositoId) > puntoPedido);
            }

            function replaceItem(item) {
                const index = items.findIndex(current => Number(current.id) === Number(item.id));

                if (index >= 0) {
                    items[index] = item;
                } else {
                    items.push(item);
                }
            }

            function updateState() {
                emptyItems.classList.toggle('d-none', items.length > 0);
                storePedidoBtn.disabled = items.length === 0;
                clearItemsBtn.disabled = items.length === 0;
            }

            function renderArticulos() {
                const term = articuloSearch.value.trim().toLowerCase();
                const filtered = articulos.filter(articulo => [articulo.nombre, articulo.codigo_producto, getArticuloUnidad(articulo)].join(' ').toLowerCase().includes(term));
                const totalPages = Math.max(1, Math.ceil(filtered.length / articulosPerPage));

                if (articulosPage > totalPages) {
                    articulosPage = totalPages;
                }

                const start = (articulosPage - 1) * articulosPerPage;
                const visible = filtered.slice(start, start + articulosPerPage);
                articulosTableBody.innerHTML = '';

                visible.forEach(articulo => {
                    const depositoId = depositoSelect.value;
                    const stockActual = depositoId ? getStockActual(articulo.id, depositoId) : null;
                    const puntoPedido = getPuntoPedido(articulo);
                    const bloqueado = isArticuloBloqueado(articulo);
                    const sobrePunto = isArticuloSobrePuntoPedido(articulo);
                    const row = document.createElement('tr');
                    row.style.cursor = bloqueado ? 'not-allowed' : 'pointer';
                    row.classList.toggle('text-muted', bloqueado);
                    row.innerHTML = `
                        <td><div class="fw-semibold">${escapeHtml(articulo.nombre)}</div></td>
                        <td>${escapeHtml(articulo.codigo_producto || '-')}</td>
                        <td>${escapeHtml(getArticuloUnidad(articulo) || '-')}</td>
                        <td>${depositoId ? escapeHtml(stockActual) : '-'}</td>
                        <td>${puntoPedido > 0 ? escapeHtml(puntoPedido) : '-'}</td>
                        <td>
                            ${bloqueado
                                ? '<span class="badge bg-light-secondary">Stock suficiente</span>'
                                : (canBypassStockBlock && sobrePunto)
                                    ? '<span class="badge bg-light-warning text-dark">Autorizado por jefe de compras</span>'
                                    : '<span class="badge bg-light-success">Se puede pedir</span>'}
                        </td>
                    `;
                    row.addEventListener('click', () => addArticulo(articulo.id));
                    articulosTableBody.appendChild(row);
                });

                emptyArticulos.classList.toggle('d-none', filtered.length > 0);
                renderPagination(filtered.length, totalPages, start, visible.length);
            }

            function renderPagination(totalItems, totalPages, start, visibleCount) {
                articulosPagination.innerHTML = '';
                articulosPaginationInfo.textContent = totalItems === 0 ? 'Sin articulos' : `Mostrando ${start + 1} a ${start + visibleCount} de ${totalItems} articulos`;

                ['prev', ...getVisiblePageNumbers(totalPages), 'next'].forEach(value => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn ' + (value === articulosPage ? 'btn-primary' : 'btn-outline-secondary');
                    button.innerHTML = value === 'prev' ? '<i class="bi bi-chevron-left"></i>' : value === 'next' ? '<i class="bi bi-chevron-right"></i>' : value;
                    button.disabled = (value === 'prev' && articulosPage === 1) || (value === 'next' && articulosPage === totalPages);
                    button.addEventListener('click', function () {
                        articulosPage = value === 'prev' ? articulosPage - 1 : value === 'next' ? articulosPage + 1 : value;
                        renderArticulos();
                    });
                    articulosPagination.appendChild(button);
                });
            }

            function getVisiblePageNumbers(totalPages) {
                const maxVisiblePages = 3;

                if (totalPages <= maxVisiblePages) {
                    return Array.from({ length: totalPages }, (_, index) => index + 1);
                }

                const halfWindow = Math.floor(maxVisiblePages / 2);
                let firstPage = Math.max(1, articulosPage - halfWindow);
                let lastPage = firstPage + maxVisiblePages - 1;

                if (lastPage > totalPages) {
                    lastPage = totalPages;
                    firstPage = totalPages - maxVisiblePages + 1;
                }

                return Array.from({ length: lastPage - firstPage + 1 }, (_, index) => firstPage + index);
            }

            function renderItems() {
                tableBody.innerHTML = '';

                items.forEach(item => {
                    const articulo = getItemArticulo(item);
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="fw-semibold">${escapeHtml(articulo.nombre || 'N/A')}</div>
                            <small class="text-muted">${escapeHtml(getArticuloUnidad(articulo) || '-')}</small>
                        </td>
                        <td>
                            <input type="number" min="1" step="1" class="form-control item-cantidad" data-id="${item.id}" value="${Number(item.cantidad || 1)}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger item-remove" data-id="${item.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

                updateState();
            }

            function addArticulo(articuloId) {
                if (!depositoSelect.value) {
                    showAlert('Seleccione un deposito antes de agregar articulos.', 'warning');
                    depositoSelect.focus();
                    return;
                }

                const articulo = articulos.find(item => Number(item.id) === Number(articuloId));

                if (articulo && isArticuloBloqueado(articulo)) {
                    showAlert('No se puede pedir este articulo porque el stock esta por encima del punto de pedido.', 'warning');
                    return;
                }

                if (items.some(item => Number(item.articulo_id) === Number(articuloId))) {
                    showAlert('El articulo ya esta cargado en el pedido.', 'warning');
                    return;
                }

                requestJson(addUrl, 'POST', { deposito_id: depositoSelect.value, articulo_id: articuloId })
                    .then(data => {
                        replaceItem(data.item);
                        renderItems();
                    })
                    .catch(error => showAlert(error.message || 'No se pudo agregar el articulo.', 'danger'));
            }

            function updateItem(itemId, payload) {
                requestJson(updateUrlTemplate.replace('__ITEM_ID__', itemId), 'PUT', payload)
                    .then(data => {
                        replaceItem(data.item);
                        renderItems();
                    })
                    .catch(error => {
                        renderItems();
                        showAlert(error.message || 'No se pudo actualizar el item.', 'danger');
                    });
            }

            function removeItem(itemId) {
                requestJson(removeUrlTemplate.replace('__ITEM_ID__', itemId), 'DELETE')
                    .then(() => {
                        items = items.filter(item => Number(item.id) !== Number(itemId));
                        renderItems();
                    })
                    .catch(error => showAlert(error.message || 'No se pudo eliminar el articulo.', 'danger'));
            }

            articuloSearch.addEventListener('input', function () {
                articulosPage = 1;
                renderArticulos();
            });

            depositoSelect.addEventListener('change', function () {
                renderArticulos();
            });

            tableBody.addEventListener('change', function (event) {
                if (event.target.classList.contains('item-cantidad')) {
                    updateItem(event.target.dataset.id, { cantidad: event.target.value });
                }
            });

            tableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.item-remove');

                if (button) {
                    removeItem(button.dataset.id);
                }
            });

            clearItemsBtn.addEventListener('click', function () {
                requestJson(clearUrl, 'DELETE')
                    .then(() => {
                        items = [];
                        renderItems();
                    })
                    .catch(error => showAlert(error.message || 'No se pudo limpiar el pedido.', 'danger'));
            });

            renderArticulos();
            renderItems();
        });
    </script>
@endpush
