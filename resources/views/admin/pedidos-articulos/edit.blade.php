@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar pedido de articulos #{{ $pedido->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza los datos principales y el detalle de articulos.</p>
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
                    <form method="POST" action="{{ route('admin.pedidos-articulos.update', $pedido->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="deposito_id" class="form-label">Deposito (*)</label>
                                <select name="deposito_id" id="deposito_id" class="form-select" required>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected(old('deposito_id', $pedido->deposito_id) == $deposito->id)>
                                            {{ $deposito->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('deposito_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="fecha_pedido" class="form-label">Fecha (*)</label>
                                <input type="date" name="fecha_pedido" id="fecha_pedido" class="form-control"
                                    value="{{ old('fecha_pedido', $pedido->fecha_pedido?->format('Y-m-d')) }}" required>
                                @error('fecha_pedido')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="estado" class="form-label">Estado (*)</label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="pendiente" @selected(old('estado', $pedido->estado) === 'pendiente')>Pendiente</option>
                                    <option value="confirmado" @selected(old('estado', $pedido->estado) === 'confirmado')>Confirmado</option>
                                    <option value="ingresado" @selected(old('estado', $pedido->estado) === 'ingresado')>Ingresado</option>
                                    <option value="cancelado" @selected(old('estado', $pedido->estado) === 'cancelado')>Cancelado</option>
                                </select>
                                @error('estado')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea name="notas" id="notas" class="form-control" rows="3">{{ old('notas', $pedido->notas) }}</textarea>
                                @error('notas')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.pedidos-articulos.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
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
                <div class="card-header">
                    <h4 class="card-title mb-0">Detalle de articulos</h4>
                </div>
                <div class="card-body">
                    <div id="detalleAlert" class="alert d-none" role="alert"></div>

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th style="width: 170px;">Cantidad</th>
                                    <th style="width: 90px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detallesTableBody"></tbody>
                        </table>
                    </div>

                    <div id="emptyDetalles" class="text-center text-muted py-4 d-none">No hay detalles cargados para este pedido.</div>
                </div>
            </div>
        </section>
    </div>

    @php
        $detalleUrlPlaceholder = '__DETALLE_ID__';
        $pedidoArticulosEditData = [
            'articulos' => $articulos,
            'stockPorDeposito' => $stockPorDeposito,
            'canBypassStockBlock' => $canBypassStockBlock,
            'detalles' => $pedido->detalles,
        ];
    @endphp

    <div id="editPedidoConfig"
        data-add-url="{{ route('admin.pedidos-articulos.detalles.store', ['id' => $pedido->id]) }}"
        data-update-url-template="{{ route('admin.pedidos-articulos.detalles.update', ['id' => $pedido->id, 'detalleId' => $detalleUrlPlaceholder]) }}"
        data-remove-url-template="{{ route('admin.pedidos-articulos.detalles.destroy', ['id' => $pedido->id, 'detalleId' => $detalleUrlPlaceholder]) }}">
    </div>
@endsection

<script type="application/json" id="pedido-articulos-edit-data">@json($pedidoArticulosEditData)</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = document.getElementById('editPedidoConfig');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const dataElement = document.getElementById('pedido-articulos-edit-data');
            let initialData = {
                articulos: [],
                stockPorDeposito: {},
                canBypassStockBlock: false,
                detalles: [],
            };

            try {
                initialData = JSON.parse(dataElement?.textContent || '{}');
            } catch (error) {
                initialData = {
                    articulos: [],
                    stockPorDeposito: {},
                    canBypassStockBlock: false,
                    detalles: [],
                };
            }

            const articulos = Array.isArray(initialData.articulos) ? initialData.articulos : [];
            const stockPorDeposito = initialData.stockPorDeposito || {};
            const canBypassStockBlock = Boolean(initialData.canBypassStockBlock);
            let detalles = Array.isArray(initialData.detalles) ? initialData.detalles : [];

            const addUrl = config.dataset.addUrl;
            const updateUrlTemplate = config.dataset.updateUrlTemplate;
            const removeUrlTemplate = config.dataset.removeUrlTemplate;
            const depositoSelect = document.getElementById('deposito_id');
            const articuloSearch = document.getElementById('articuloSearch');
            const articulosTableBody = document.getElementById('articulosTableBody');
            const emptyArticulos = document.getElementById('emptyArticulos');
            const articulosPagination = document.getElementById('articulosPagination');
            const articulosPaginationInfo = document.getElementById('articulosPaginationInfo');
            const detallesTableBody = document.getElementById('detallesTableBody');
            const emptyDetalles = document.getElementById('emptyDetalles');
            const detalleAlert = document.getElementById('detalleAlert');
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
                detalleAlert.className = 'alert alert-' + type;
                detalleAlert.textContent = message;
                detalleAlert.classList.remove('d-none');
                window.setTimeout(() => detalleAlert.classList.add('d-none'), 3000);
            }

            function getArticuloUnidad(articulo) {
                return articulo?.unidad_medida?.nombre || articulo?.unidadMedida?.nombre || '';
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

            function replaceDetalle(detalle) {
                const index = detalles.findIndex(item => Number(item.id) === Number(detalle.id));

                if (index >= 0) {
                    detalles[index] = detalle;
                } else {
                    detalles.push(detalle);
                }
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

            function renderDetalles() {
                detallesTableBody.innerHTML = '';

                detalles.forEach(detalle => {
                    const articulo = detalle.articulo || {};
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="fw-semibold">${escapeHtml(articulo.nombre || 'N/A')}</div>
                            <small class="text-muted">${escapeHtml(getArticuloUnidad(articulo) || '-')}</small>
                        </td>
                        <td>
                            <input type="number" min="1" step="1" class="form-control detalle-cantidad" data-id="${detalle.id}" value="${Number(detalle.cantidad || 1)}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger detalle-remove" data-id="${detalle.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    detallesTableBody.appendChild(row);
                });

                emptyDetalles.classList.toggle('d-none', detalles.length > 0);
            }

            function addArticulo(articuloId) {
                const articulo = articulos.find(item => Number(item.id) === Number(articuloId));

                if (articulo && isArticuloBloqueado(articulo)) {
                    showAlert('No se puede pedir este articulo porque el stock esta por encima del punto de pedido.', 'warning');
                    return;
                }

                if (detalles.some(detalle => Number(detalle.articulo_id) === Number(articuloId))) {
                    showAlert('El articulo ya esta cargado en el pedido.', 'warning');
                    return;
                }

                requestJson(addUrl, 'POST', { articulo_id: articuloId })
                    .then(data => {
                        replaceDetalle(data.detalle);
                        renderDetalles();
                    })
                    .catch(error => showAlert(error.message || 'No se pudo agregar el articulo.', 'danger'));
            }

            function updateDetalle(detalleId, payload) {
                requestJson(updateUrlTemplate.replace('__DETALLE_ID__', detalleId), 'PUT', payload)
                    .then(data => {
                        replaceDetalle(data.detalle);
                        renderDetalles();
                    })
                    .catch(error => {
                        renderDetalles();
                        showAlert(error.message || 'No se pudo actualizar el detalle.', 'danger');
                    });
            }

            function removeDetalle(detalleId) {
                requestJson(removeUrlTemplate.replace('__DETALLE_ID__', detalleId), 'DELETE')
                    .then(() => {
                        detalles = detalles.filter(detalle => Number(detalle.id) !== Number(detalleId));
                        renderDetalles();
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

            detallesTableBody.addEventListener('change', function (event) {
                if (event.target.classList.contains('detalle-cantidad')) {
                    updateDetalle(event.target.dataset.id, { cantidad: event.target.value });
                }
            });

            detallesTableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.detalle-remove');

                if (button) {
                    removeDetalle(button.dataset.id);
                }
            });

            renderArticulos();
            renderDetalles();
        });
    </script>
@endpush
