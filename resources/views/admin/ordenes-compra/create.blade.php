@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Nueva orden de compra</h3>
                <p class="text-subtitle text-muted">Carga articulos, cantidades y precios para preparar la compra.</p>
            </div>
            <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">
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

            @if (session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Datos de la compra</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.ordenes-compra.create') }}" class="row g-3 align-items-end mb-3">
                                <div class="col-12 col-md-9">
                                    <label for="pedido_articulo_id_select" class="form-label">Pedido de articulos</label>
                                    <select id="pedido_articulo_id_select" name="pedido_articulo_id" class="form-select">
                                        <option value="">Sin pedido relacionado</option>
                                        @foreach ($pedidos as $pedido)
                                            <option value="{{ $pedido->id }}" @selected(optional($selectedPedido)->id === $pedido->id)>
                                                Pedido #{{ $pedido->id }} - {{ $pedido->deposito?->nombre ?? 'N/A' }} - {{ $pedido->fecha_pedido?->format('d/m/Y') }} - {{ $pedido->detalles->count() }} articulo(s)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-box-arrow-in-down"></i> Cargar pedido
                                    </button>
                                </div>
                            </form>

                            @if ($selectedPedido)
                                <div class="alert alert-info py-2">
                                    Orden basada en el pedido
                                    <a href="{{ route('admin.pedidos-articulos.show', $selectedPedido->id) }}" class="alert-link">
                                        #{{ $selectedPedido->id }}
                                    </a>.
                                    Seleccione los proveedores en el detalle para generar la orden de compra.
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-12">
                                <label for="deposito_id" class="form-label">Deposito (*)</label>
                                <select id="deposito_id" class="form-select" required>
                                    <option value="">Seleccione deposito</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected(optional($selectedPedido)->deposito_id === $deposito->id)>{{ $deposito->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Articulos</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="articuloSearch" class="form-label">Buscar articulo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" id="articuloSearch" class="form-control"
                                        placeholder="Nombre, codigo o unidad">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Articulo</th>
                                            <th>Codigo</th>
                                            <th>Unidad</th>
                                        </tr>
                                    </thead>
                                    <tbody id="articulosTableBody"></tbody>
                                </table>
                            </div>

                            <div id="emptyArticulos" class="text-center text-muted py-4 d-none">
                                No hay articulos para mostrar.
                            </div>

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <small id="articulosPaginationInfo" class="text-muted"></small>
                                <div id="articulosPagination" class="btn-group btn-group-sm" role="group" aria-label="Paginacion de articulos"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
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
                                            <th style="width: 240px;">Proveedor</th>
                                            <th style="width: 130px;">Cantidad</th>
                                            <th style="width: 160px;">Precio</th>
                                            <th style="width: 160px;">Subtotal</th>
                                            <th class="text-end" style="width: 90px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">Total</th>
                                            <th id="itemsTotal">$0,00</th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end text-muted">
                                                Importes expresados sin impuestos.
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div id="emptyItems" class="text-center text-muted py-4 d-none">
                                No hay articulos agregados.
                            </div>

                            <form id="storeCompraForm" method="POST" action="{{ route('admin.ordenes-compra.store') }}" class="d-flex justify-content-end gap-2 mt-3">
                                @csrf
                                @if ($selectedPedido)
                                    <input type="hidden" name="pedido_articulo_id" value="{{ $selectedPedido->id }}">
                                @endif
                                <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">Cancelar</a>
                                <button type="submit" id="storeCompraBtn" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Guardar orden
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div id="ordenCompraConfig"
        class="d-none"
        data-csrf-token="{{ csrf_token() }}"
        data-add-url="{{ route('admin.ordenes-compra.additem') }}"
        data-update-url-template="{{ route('admin.ordenes-compra.updateitem', ['itemid' => '__ITEM_ID__']) }}"
        data-remove-url-template="{{ route('admin.ordenes-compra.removeitem', ['itemid' => '__ITEM_ID__']) }}"
        data-clear-url="{{ route('admin.ordenes-compra.clearitem') }}"
        data-items="{{ $items->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
        data-articulos="{{ $articulos->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
        data-proveedores="{{ $proveedores->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}">
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = document.getElementById('ordenCompraConfig');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || config.dataset.csrfToken;
            const addUrl = config.dataset.addUrl;
            const updateUrlTemplate = config.dataset.updateUrlTemplate;
            const removeUrlTemplate = config.dataset.removeUrlTemplate;
            const clearUrl = config.dataset.clearUrl;
            const items = JSON.parse(config.dataset.items || '[]');
            const articulos = JSON.parse(config.dataset.articulos || '[]');
            const proveedores = JSON.parse(config.dataset.proveedores || '[]');

            const depositoSelect = document.getElementById('deposito_id');
            const articuloSearch = document.getElementById('articuloSearch');
            const articulosTableBody = document.getElementById('articulosTableBody');
            const emptyArticulos = document.getElementById('emptyArticulos');
            const articulosPagination = document.getElementById('articulosPagination');
            const articulosPaginationInfo = document.getElementById('articulosPaginationInfo');
            const clearItemsBtn = document.getElementById('clearItemsBtn');
            const tableBody = document.getElementById('itemsTableBody');
            const emptyItems = document.getElementById('emptyItems');
            const itemsTotal = document.getElementById('itemsTotal');
            const itemsAlert = document.getElementById('itemsAlert');
            const storeCompraBtn = document.getElementById('storeCompraBtn');

            const articulosPerPage = 8;
            let articulosPage = 1;

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function money(value) {
                return new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS'
                }).format(Number(value || 0));
            }

            function parseMoney(value) {
                const raw = String(value ?? '').trim();

                if (raw === '') {
                    return 0;
                }

                if (raw.includes(',')) {
                    return Number(raw.replace(/\./g, '').replace(',', '.')) || 0;
                }

                if (/^\d{1,3}(\.\d{3})+$/.test(raw)) {
                    return Number(raw.replace(/\./g, '')) || 0;
                }

                return Number(raw) || 0;
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

            function getItemArticulo(item) {
                return item.articulos || item.articulo || {};
            }

            function getSubtotal(item) {
                return parseMoney(item.precio_compra_unidad) * Number(item.cantidad || 0);
            }

            function getProveedorOptions(selectedProveedorId) {
                const selectedId = String(selectedProveedorId || '');
                const options = ['<option value="">Sin proveedor</option>'];

                proveedores.forEach(proveedor => {
                    const value = String(proveedor.id);
                    const selected = value === selectedId ? ' selected' : '';
                    options.push(`<option value="${value}"${selected}>${escapeHtml(proveedor.nombre)}</option>`);
                });

                return options.join('');
            }

            function updateTotals() {
                const total = items.reduce((sum, item) => sum + getSubtotal(item), 0);
                itemsTotal.textContent = money(total);
                emptyItems.classList.toggle('d-none', items.length > 0);
                storeCompraBtn.disabled = items.length === 0;
            }

            function getArticuloUnidad(articulo) {
                return articulo.unidad_medida?.nombre || articulo.unidadMedida?.nombre || '';
            }

            function renderArticulos() {
                const term = articuloSearch.value.trim().toLowerCase();
                const filtered = articulos.filter(articulo => {
                    const unidad = getArticuloUnidad(articulo);
                    const haystack = [
                        articulo.nombre,
                        articulo.codigo_producto,
                        unidad
                    ].join(' ').toLowerCase();

                    return haystack.includes(term);
                });
                const totalPages = Math.max(1, Math.ceil(filtered.length / articulosPerPage));

                if (articulosPage > totalPages) {
                    articulosPage = totalPages;
                }

                const start = (articulosPage - 1) * articulosPerPage;
                const visible = filtered.slice(start, start + articulosPerPage);

                articulosTableBody.innerHTML = '';

                visible.forEach(articulo => {
                    const row = document.createElement('tr');
                    row.className = 'cursor-pointer';
                    row.style.cursor = 'pointer';
                    row.innerHTML = `
                        <td>
                            <div class="fw-semibold">${escapeHtml(articulo.nombre)}</div>
                        </td>
                        <td>${escapeHtml(articulo.codigo_producto || '-')}</td>
                        <td>${escapeHtml(getArticuloUnidad(articulo) || '-')}</td>
                    `;

                    row.addEventListener('click', function () {
                        addArticulo(articulo.id);
                    });

                    articulosTableBody.appendChild(row);
                });

                emptyArticulos.classList.toggle('d-none', filtered.length > 0);
                renderArticulosPagination(filtered.length, totalPages, start, visible.length);
            }

            function renderArticulosPagination(totalItems, totalPages, start, visibleCount) {
                articulosPagination.innerHTML = '';

                if (totalItems === 0) {
                    articulosPaginationInfo.textContent = 'Sin articulos';
                    return;
                }

                articulosPaginationInfo.textContent = `Mostrando ${start + 1} a ${start + visibleCount} de ${totalItems} articulos`;

                const prevButton = document.createElement('button');
                prevButton.type = 'button';
                prevButton.className = 'btn btn-outline-secondary';
                prevButton.innerHTML = '<i class="bi bi-chevron-left"></i>';
                prevButton.disabled = articulosPage === 1;
                prevButton.addEventListener('click', function () {
                    articulosPage -= 1;
                    renderArticulos();
                });
                articulosPagination.appendChild(prevButton);

                getVisiblePageNumbers(totalPages).forEach(page => {
                    const pageButton = document.createElement('button');
                    pageButton.type = 'button';
                    pageButton.className = page === articulosPage ? 'btn btn-primary' : 'btn btn-outline-secondary';
                    pageButton.textContent = page;
                    pageButton.addEventListener('click', function () {
                        articulosPage = page;
                        renderArticulos();
                    });
                    articulosPagination.appendChild(pageButton);
                });

                const nextButton = document.createElement('button');
                nextButton.type = 'button';
                nextButton.className = 'btn btn-outline-secondary';
                nextButton.innerHTML = '<i class="bi bi-chevron-right"></i>';
                nextButton.disabled = articulosPage === totalPages;
                nextButton.addEventListener('click', function () {
                    articulosPage += 1;
                    renderArticulos();
                });
                articulosPagination.appendChild(nextButton);
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
                let activeFieldInfo = null;
                const activeElement = document.activeElement;

                if (activeElement instanceof HTMLInputElement) {
                    const activeRow = activeElement.closest('tr');
                    if (activeRow && activeRow.dataset.itemId) {
                        if (activeElement.classList.contains('item-cantidad')) {
                            activeFieldInfo = {
                                itemId: activeRow.dataset.itemId,
                                field: 'cantidad',
                                selectionStart: activeElement.selectionStart,
                                selectionEnd: activeElement.selectionEnd,
                            };
                        } else if (activeElement.classList.contains('item-precio')) {
                            activeFieldInfo = {
                                itemId: activeRow.dataset.itemId,
                                field: 'precio',
                                selectionStart: activeElement.selectionStart,
                                selectionEnd: activeElement.selectionEnd,
                            };
                        }
                    }
                }

                tableBody.innerHTML = '';

                items.forEach(item => {
                    const articulo = getItemArticulo(item);
                    const unidad = articulo.unidad_medida?.nombre || articulo.unidadMedida?.nombre || '';
                    const row = document.createElement('tr');
                    row.dataset.itemId = item.id;
                    row.innerHTML = `
                        <td>
                            <div class="fw-semibold">${escapeHtml(articulo.nombre || 'Articulo')}</div>
                            <small class="text-muted">${escapeHtml(articulo.codigo_producto || '')}${unidad ? ' - ' + escapeHtml(unidad) : ''}</small>
                        </td>
                        <td>
                            <select class="form-select form-select-sm item-proveedor">
                                ${getProveedorOptions(item.proveedor_id)}
                            </select>
                        </td>
                        <td>
                            <input type="number" min="1" step="1" class="form-control form-control-sm item-cantidad" value="${item.cantidad || 1}">
                        </td>
                        <td>
                            <input type="text" inputmode="decimal" class="form-control form-control-sm item-precio" value="${escapeHtml(item.precio_compra_unidad || 0)}">
                        </td>
                        <td class="item-subtotal">${money(getSubtotal(item))}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-danger item-remove" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;

                    row.querySelector('.item-cantidad').addEventListener('change', function () {
                        updateItem(item.id, { cantidad: this.value });
                    });

                    row.querySelector('.item-proveedor').addEventListener('change', function () {
                        updateItem(item.id, { proveedor_id: this.value || null });
                    });

                    row.querySelector('.item-precio').addEventListener('change', function () {
                        const precio = parseMoney(this.value);
                        this.value = precio.toFixed(2);
                        updateItem(item.id, { precio_compra_unidad: precio.toFixed(2) });
                    });

                    row.querySelector('.item-remove').addEventListener('click', function () {
                        removeItem(item.id);
                    });

                    tableBody.appendChild(row);
                });

                if (activeFieldInfo) {
                    const activeRow = tableBody.querySelector(`tr[data-item-id="${activeFieldInfo.itemId}"]`);
                    const input = activeRow?.querySelector(`.item-${activeFieldInfo.field}`);
                    if (input) {
                        input.focus();
                        if (typeof input.setSelectionRange === 'function' && activeFieldInfo.selectionStart !== null && activeFieldInfo.selectionEnd !== null) {
                            input.setSelectionRange(activeFieldInfo.selectionStart, activeFieldInfo.selectionEnd);
                        }
                    }
                }

                updateTotals();
            }

            function replaceItem(updatedItem) {
                const index = items.findIndex(item => Number(item.id) === Number(updatedItem.id));
                if (index === -1) {
                    items.push(updatedItem);
                    return;
                }
                items[index] = updatedItem;
            }

            function updateItem(itemId, payload) {
                const url = updateUrlTemplate.replace('__ITEM_ID__', itemId);
                requestJson(url, 'PUT', payload)
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
                const url = removeUrlTemplate.replace('__ITEM_ID__', itemId);
                requestJson(url, 'DELETE')
                    .then(data => {
                        const index = items.findIndex(item => Number(item.id) === Number(itemId));
                        if (index !== -1) {
                            items.splice(index, 1);
                        }
                        renderItems();
                    })
                    .catch(error => {
                        showAlert(error.message || 'No se pudo eliminar el item.', 'danger');
                    });
            }

            function addArticulo(articuloId) {
                if (!depositoSelect.value) {
                    showAlert('Seleccione un deposito antes de agregar articulos.', 'warning');
                    depositoSelect.focus();
                    return;
                }

                requestJson(addUrl, 'POST', {
                    deposito_id: depositoSelect.value,
                    articulo_id: articuloId,
                    proveedor_id: null,
                })
                    .then(data => {
                        replaceItem(data.item);
                        renderItems();
                    })
                    .catch(error => {
                        showAlert(error.message || 'No se pudo agregar el item.', 'danger');
                    });
            }

            articuloSearch.addEventListener('input', function () {
                articulosPage = 1;
                renderArticulos();
            });

            articulosTableBody.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }
            });

            clearItemsBtn.addEventListener('click', function () {
                if (items.length === 0) {
                    return;
                }

                requestJson(clearUrl, 'DELETE')
                    .then(data => {
                        items.splice(0, items.length);
                        renderItems();
                    })
                    .catch(error => {
                        showAlert(error.message || 'No se pudo limpiar la compra temporal.', 'danger');
                    });
            });

            renderArticulos();
            renderItems();
        });
    </script>
@endpush
