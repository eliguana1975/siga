@extends('layouts.admin')

@push('styles')
    <style>
        .provider-tax-summary {
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            padding: 1rem;
        }

        .provider-tax-item + .provider-tax-item {
            border-top: 1px solid var(--bs-border-color);
            margin-top: .75rem;
            padding-top: .75rem;
        }

        .provider-tax-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin: .25rem .35rem .25rem 0;
            padding: .25rem .5rem;
            border: 1px solid var(--bs-border-color);
            border-radius: .35rem;
            font-size: .82rem;
        }

    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Editar orden de compra #{{ $compra->id }}</h3>
                <p class="text-subtitle text-muted">Actualiza los datos principales y consulta el detalle de articulos.</p>
            </div>
            <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Datos de la compra</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ordenes-compra.update', $compra->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="deposito_id" class="form-label">Deposito (*)</label>
                                <select name="deposito_id" id="deposito_id" class="form-select" required>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected(old('deposito_id', $compra->deposito_id) == $deposito->id)>
                                            {{ $deposito->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('deposito_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="proveedor_id" class="form-label">Proveedor predeterminado</label>
                                <select name="proveedor_id" id="proveedor_id" class="form-select">
                                    <option value="">Sin proveedor</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" @selected(old('proveedor_id', $compra->proveedor_id) == $proveedor->id)>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pedido_articulo_id" class="form-label">Pedido de articulos</label>
                                <select name="pedido_articulo_id" id="pedido_articulo_id" class="form-select">
                                    <option value="">Sin pedido relacionado</option>
                                    @foreach ($pedidos as $pedido)
                                        <option value="{{ $pedido->id }}" @selected(old('pedido_articulo_id', $compra->pedido_articulo_id) == $pedido->id)>
                                            Pedido #{{ $pedido->id }} - {{ $pedido->deposito?->nombre ?? 'N/A' }} - {{ $pedido->fecha_pedido?->format('d/m/Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pedido_articulo_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="fecha_compra" class="form-label">Fecha (*)</label>
                                <input type="date" name="fecha_compra" id="fecha_compra" class="form-control"
                                    value="{{ old('fecha_compra', $compra->fecha_compra?->format('Y-m-d')) }}" required>
                                @error('fecha_compra')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="estado" class="form-label">Estado (*)</label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="pendiente" @selected(old('estado', $compra->estado) === 'pendiente')>Pendiente</option>
                                    <option value="aprobada" @selected(old('estado', $compra->estado) === 'aprobada')>Aprobada</option>
                                    <option value="recibido" @selected(old('estado', $compra->estado) === 'recibido')>Recibido</option>
                                    <option value="cancelado" @selected(old('estado', $compra->estado) === 'cancelado')>Cancelado</option>
                                </select>
                                @error('estado')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="forma_pago" class="form-label">Forma de pago</label>
                                <select name="forma_pago" id="forma_pago" class="form-select">
                                    <option value="">Sin definir</option>
                                    @foreach (\App\Models\Compra::formasPago() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('forma_pago', $compra->forma_pago) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('forma_pago')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="datos_pago" class="form-label">Datos de pago</label>
                                <textarea name="datos_pago" id="datos_pago" class="form-control" rows="2" placeholder="CBU, alias, banco, cheque o condiciones">{{ old('datos_pago', $compra->datos_pago) }}</textarea>
                                @error('datos_pago')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="provider-tax-summary">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <h6 class="mb-0">Impuestos de proveedores</h6>
                                        <small class="text-muted">Segun proveedor predeterminado y detalle</small>
                                    </div>
                                    <div id="proveedorImpuestosResumen" class="text-muted small">
                                        No hay proveedores con impuestos activos en esta orden.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea name="notas" id="notas" class="form-control" rows="3">{{ old('notas', $compra->notas) }}</textarea>
                                @error('notas')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
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
                                    <th style="width: 240px;">Proveedor</th>
                                    <th style="width: 160px;">Cantidad</th>
                                    <th style="width: 190px;">Precio</th>
                                    <th style="width: 170px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detallesTableBody"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Base</th>
                                    <th id="detallesBaseTotal">$ 0,00</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Impuestos</th>
                                    <th id="detallesImpuestosTotal">$ 0,00</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Total con impuestos</th>
                                    <th id="detallesTotal">$ 0,00</th>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end text-muted">
                                        Los impuestos se calculan segun el proveedor asignado a cada articulo.
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div id="emptyDetalles" class="text-center text-muted py-4 d-none">
                        No hay detalles cargados para esta orden.
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div id="editCompraConfig"
        data-add-url="{{ route('admin.ordenes-compra.detalles.store', $compra->id) }}"
        data-update-url-template="{{ route('admin.ordenes-compra.detalles.update', ['id' => $compra->id, 'detalleId' => '__DETALLE_ID__']) }}"
        data-remove-url-template="{{ route('admin.ordenes-compra.detalles.destroy', ['id' => $compra->id, 'detalleId' => '__DETALLE_ID__']) }}">
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = document.getElementById('editCompraConfig');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const articulos = @json($articulos);
            const proveedores = @json($proveedores);
            let detalles = @json($compra->detalles);

            const addUrl = config.dataset.addUrl;
            const updateUrlTemplate = config.dataset.updateUrlTemplate;
            const removeUrlTemplate = config.dataset.removeUrlTemplate;

            const proveedorSelect = document.getElementById('proveedor_id');
            const formaPagoSelect = document.getElementById('forma_pago');
            const datosPagoInput = document.getElementById('datos_pago');
            const detallesTableBody = document.getElementById('detallesTableBody');
            const emptyDetalles = document.getElementById('emptyDetalles');
            const detallesBaseTotal = document.getElementById('detallesBaseTotal');
            const detallesImpuestosTotal = document.getElementById('detallesImpuestosTotal');
            const detallesTotal = document.getElementById('detallesTotal');
            const detalleAlert = document.getElementById('detalleAlert');

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
                detalleAlert.className = 'alert alert-' + type;
                detalleAlert.textContent = message;
                detalleAlert.classList.remove('d-none');
                window.setTimeout(() => detalleAlert.classList.add('d-none'), 3000);
            }

            function getArticuloUnidad(articulo) {
                return articulo?.unidad_medida?.nombre || articulo?.unidadMedida?.nombre || '';
            }

            function getDetalleArticulo(detalle) {
                return detalle.articulo || {};
            }

            function getSubtotal(detalle) {
                return parseMoney(detalle.precio_compra_unidad) * Number(detalle.cantidad || 0);
            }

            function getDetalleProveedor(detalle) {
                return proveedorById(detalle.proveedor_id || proveedorSelect.value);
            }

            function getDetalleImpuestos(detalle) {
                return getProveedorImpuestos(getDetalleProveedor(detalle));
            }

            function getDetalleImpuestoImporte(detalle) {
                const subtotal = getSubtotal(detalle);
                const porcentaje = getDetalleImpuestos(detalle)
                    .reduce((sum, impuesto) => sum + parseMoney(impuesto.porcentaje), 0);

                return subtotal * porcentaje / 100;
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

            function proveedorById(proveedorId) {
                return proveedores.find(proveedor => String(proveedor.id) === String(proveedorId || '')) || null;
            }

            function getProveedorImpuestos(proveedor) {
                return (proveedor?.impuestos || []).filter(impuesto => {
                    return Boolean(impuesto?.activo ?? true) && String(impuesto?.nombre || '').trim() !== '';
                });
            }

            function renderProveedorImpuestos() {
                const container = document.getElementById('proveedorImpuestosResumen');

                if (!container) {
                    return;
                }

                const proveedorIds = [
                    proveedorSelect.value || null,
                    ...detalles.map(detalle => detalle.proveedor_id || null),
                ].filter(Boolean);

                const uniqueIds = [...new Set(proveedorIds.map(String))];
                const proveedoresConImpuestos = uniqueIds
                    .map(proveedorById)
                    .filter(Boolean)
                    .map(proveedor => ({
                        proveedor,
                        impuestos: getProveedorImpuestos(proveedor),
                    }))
                    .filter(item => item.impuestos.length > 0);

                if (proveedoresConImpuestos.length === 0) {
                    container.className = 'text-muted small';
                    container.textContent = 'No hay proveedores con impuestos activos en esta orden.';
                    return;
                }

                container.className = '';
                container.innerHTML = proveedoresConImpuestos.map(item => {
                    const chips = item.impuestos.map(impuesto => `
                        <span class="provider-tax-chip">
                            <strong>${escapeHtml(impuesto.nombre)}</strong>
                            <span>${escapeHtml(impuesto.porcentaje ?? 0)}%</span>
                            ${impuesto.descripcion ? `<small class="text-muted">${escapeHtml(impuesto.descripcion)}</small>` : ''}
                        </span>
                    `).join('');

                    return `
                        <div class="provider-tax-item">
                            <div class="fw-semibold mb-1">${escapeHtml(item.proveedor.nombre)}</div>
                            <div>${chips}</div>
                        </div>
                    `;
                }).join('');
            }

            function applyProveedorPaymentDefaults() {
                const proveedor = proveedorById(proveedorSelect.value);

                if (!proveedor) {
                    return;
                }

                if (!formaPagoSelect.value && proveedor.forma_pago_preferida) {
                    formaPagoSelect.value = proveedor.forma_pago_preferida;

                    if (window.jQuery) {
                        window.jQuery(formaPagoSelect).trigger('change.select2');
                    }
                }

                if (!datosPagoInput.value.trim() && proveedor.datos_pago) {
                    datosPagoInput.value = proveedor.datos_pago;
                }
            }
            function replaceDetalle(detalle) {
                const index = detalles.findIndex(item => Number(item.id) === Number(detalle.id));

                if (index >= 0) {
                    detalles[index] = detalle;
                } else {
                    detalles.push(detalle);
                }
            }

            function updateTotals(totalFromServer = null) {
                const baseTotal = totalFromServer === null
                    ? detalles.reduce((sum, detalle) => sum + getSubtotal(detalle), 0)
                    : Number(totalFromServer || 0);
                const impuestosTotal = detalles.reduce((sum, detalle) => sum + getDetalleImpuestoImporte(detalle), 0);
                const total = baseTotal + impuestosTotal;

                if (detallesBaseTotal) {
                    detallesBaseTotal.textContent = money(baseTotal);
                }

                if (detallesImpuestosTotal) {
                    detallesImpuestosTotal.textContent = money(impuestosTotal);
                }

                detallesTotal.textContent = money(total);
                emptyDetalles.classList.toggle('d-none', detalles.length > 0);
            }

            function renderArticulos() {
                const term = articuloSearch.value.trim().toLowerCase();
                const filtered = articulos.filter(articulo => {
                    const haystack = [
                        articulo.nombre,
                        articulo.codigo_producto,
                        getArticuloUnidad(articulo)
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
                    row.style.cursor = 'pointer';
                    row.innerHTML = `
                        <td><div class="fw-semibold">${escapeHtml(articulo.nombre)}</div></td>
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
                    pageButton.className = 'btn ' + (page === articulosPage ? 'btn-primary' : 'btn-outline-secondary');
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

            function renderDetalles(totalFromServer = null) {
                detallesTableBody.innerHTML = '';

                detalles.forEach(detalle => {
                    const articulo = getDetalleArticulo(detalle);
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="fw-semibold">${escapeHtml(articulo.nombre || 'N/A')}</div>
                            <small class="text-muted">${escapeHtml(getArticuloUnidad(articulo) || '-')}</small>
                        </td>
                        <td>${escapeHtml(getDetalleProveedor(detalle)?.nombre || '-')}</td>
                        <td>${Number(detalle.cantidad || 0)}</td>
                        <td>${money(parseMoney(detalle.precio_compra_unidad))}</td>
                        <td class="fw-semibold detalle-subtotal">${money(getSubtotal(detalle))}</td>
                    `;
                    detallesTableBody.appendChild(row);
                });

                updateTotals(totalFromServer);
                renderProveedorImpuestos();
            }

            function addArticulo(articuloId) {
                const exists = detalles.some(detalle => Number(detalle.articulo_id) === Number(articuloId));

                if (exists) {
                    showAlert('El articulo ya esta cargado en la orden.', 'warning');
                    return;
                }

                requestJson(addUrl, 'POST', { articulo_id: articuloId, proveedor_id: proveedorSelect.value || null })
                    .then(data => {
                        replaceDetalle(data.detalle);
                        renderDetalles(data.total);
                    })
                    .catch(error => {
                        showAlert(error.message || 'No se pudo agregar el articulo.', 'danger');
                    });
            }

            function updateDetalle(detalleId, payload) {
                const url = updateUrlTemplate.replace('__DETALLE_ID__', detalleId);

                requestJson(url, 'PUT', payload)
                    .then(data => {
                        replaceDetalle(data.detalle);
                        renderDetalles(data.total);
                    })
                    .catch(error => {
                        renderDetalles();
                        showAlert(error.message || 'No se pudo actualizar el detalle.', 'danger');
                    });
            }

            function removeDetalle(detalleId) {
                const url = removeUrlTemplate.replace('__DETALLE_ID__', detalleId);

                requestJson(url, 'DELETE')
                    .then(data => {
                        detalles = detalles.filter(detalle => Number(detalle.id) !== Number(detalleId));
                        renderDetalles(data.total);
                    })
                    .catch(error => {
                        showAlert(error.message || 'No se pudo eliminar el articulo.', 'danger');
                    });
            }

            proveedorSelect.addEventListener('change', function () {
                applyProveedorPaymentDefaults();
                renderProveedorImpuestos();
                updateTotals();
            });

            renderDetalles();
            renderProveedorImpuestos();
        });
    </script>
@endpush
