@extends('layouts.admin')

@php
    $canCreateArticulos = auth()->user()?->can('articulos.crear');
    $canCreatePedidos = auth()->user()?->can('pedidos-articulos.crear');
    $canCreateSolicitudesRepuestos = auth()->user()?->can('solicitudes-repuestos.crear');
    $openQuickArticle = session('open_quick_article') || old('crear_articulo');
    $ordenCerrada = $orden->estaCerrada();
    $articleClassifier = app(\App\Services\ArticleClassificationService::class);
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Articulos usados</h3>
                <p class="text-subtitle text-muted">
                    Orden #{{ $orden->id }} - {{ $orden->titulo }}
                </p>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-2">
                @if ($canCreateSolicitudesRepuestos && ! $ordenCerrada)
                    <a href="{{ route('admin.solicitudes-repuestos.create', ['orden_trabajo_id' => $orden->id, 'flota_id' => $orden->flota_id]) }}"
                        class="btn btn-warning">
                        <i class="bi bi-tools"></i> Pedir repuesto
                    </a>
                @endif
                <a href="{{ route('admin.ordenes-trabajo.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Fecha</small>
                            <strong>{{ optional($orden->fecha_orden)->format('d/m/Y') }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Empleado</small>
                            <strong>{{ $orden->empleado?->apellidos }}, {{ $orden->empleado?->nombres }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Flota</small>
                            <strong>{{ $orden->flota?->nro_interno }} - {{ $orden->flota?->dominio }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Articulos</small>
                            <span class="badge bg-light-info">
                                {{ $orden->articulosUsados->count() }} item(s) / {{ $orden->articulosUsados->sum('cantidad') }} unidad(es)
                            </span>
                            <span class="badge bg-light-success">
                                ${{ number_format($orden->articulosUsados->sum(fn ($detalle) => (float) $detalle->valor_unitario * (int) $detalle->cantidad), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

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

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if ($ordenCerrada)
                        <div class="alert alert-info">
                            Esta orden de trabajo esta cerrada. Los articulos quedan solo para consulta.
                        </div>
                    @endif

                    @unless ($ordenCerrada)
                    <div class="border rounded p-3 mb-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">Registrar servicio realizado</h5>
                                <small class="text-muted">
                                    Usa el kilometraje de la orden para reiniciar el indicador del servicio correspondiente.
                                </small>
                            </div>
                            <span class="badge bg-light-info">
                                {{ $orden->kilometraje !== null ? number_format($orden->kilometraje, 0, ',', '.') . ' km' : 'Sin kilometraje' }}
                            </span>
                        </div>

                        @if ($intervalosServicio->isEmpty())
                            <div class="alert alert-warning mb-0">
                                No hay intervalos de servicio activos configurados.
                            </div>
                        @elseif (!$orden->flota_id || $orden->kilometraje === null)
                            <div class="alert alert-warning mb-0">
                                La orden necesita vehiculo y kilometraje para registrar un servicio realizado.
                            </div>
                        @else
                            @php
                                $confirmServicioRealizado = 'Registrar este servicio como realizado para el interno ' . ($orden->flota?->nro_interno ?? '') . '?';
                            @endphp
                            <form method="POST" action="{{ route('admin.ordenes-trabajo.registrar-servicio-kilometraje', $orden->id) }}"
                                data-confirm-message="{{ $confirmServicioRealizado }}"
                                onsubmit="return confirmFormSubmit(this, this.dataset.confirmMessage);">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="servicio-km" class="form-label">Servicio realizado (*)</label>
                                        <select name="configuracion_intervalo_servicio_id" id="servicio-km" class="form-select" required>
                                            <option value="">Seleccione servicio</option>
                                            @foreach ($intervalosServicio as $intervalo)
                                                <option value="{{ $intervalo->id }}" @selected((string) old('configuracion_intervalo_servicio_id') === (string) $intervalo->id)>
                                                    {{ $intervalo->etiqueta() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label for="observaciones-servicio-km" class="form-label">Observaciones</label>
                                        <input type="text" name="observaciones" id="observaciones-servicio-km"
                                            class="form-control" value="{{ old('observaciones', 'Registrado desde orden de trabajo #' . $orden->id) }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-check-circle"></i> Marcar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                    @endunless

                    @unless ($ordenCerrada)
                        @if ($servicioKits->isNotEmpty())
                            <div class="border rounded p-3 mb-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                    <div>
                                        <h5 class="mb-1">Cargar kit de servicio</h5>
                                        <small class="text-muted">
                                            Los articulos automaticos vienen seleccionados. Los manuales se pueden marcar si tambien se usaron.
                                        </small>
                                    </div>
                                    <span class="badge bg-light-primary">Interno {{ $orden->flota?->nro_interno }}</span>
                                </div>

                                <div class="accordion" id="serviceKitsAccordion">
                                    @foreach ($servicioKits as $kitIndex => $kit)
                                        @php
                                            $servicio = $kit['servicio'];
                                            $items = $kit['items'];
                                            $automaticos = $items->where('automatico', true)->count();
                                        @endphp
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="service-kit-heading-{{ $servicio->id }}">
                                                <button class="accordion-button {{ $kitIndex > 0 ? 'collapsed' : '' }}" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#service-kit-collapse-{{ $servicio->id }}"
                                                    aria-expanded="{{ $kitIndex === 0 ? 'true' : 'false' }}"
                                                    aria-controls="service-kit-collapse-{{ $servicio->id }}">
                                                    <span class="fw-semibold me-2">{{ $servicio->etiqueta() }}</span>
                                                    <span class="badge bg-light-info me-2">{{ $items->count() }} articulo(s)</span>
                                                    <span class="badge bg-light-success">{{ $automaticos }} automatico(s)</span>
                                                </button>
                                            </h2>
                                            <div id="service-kit-collapse-{{ $servicio->id }}"
                                                class="accordion-collapse collapse {{ $kitIndex === 0 ? 'show' : '' }}"
                                                aria-labelledby="service-kit-heading-{{ $servicio->id }}"
                                                data-bs-parent="#serviceKitsAccordion">
                                                <div class="accordion-body">
                                                    <form method="POST" action="{{ route('admin.ordenes-trabajo.articulos.kit-servicio', $orden->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="configuracion_intervalo_servicio_id" value="{{ $servicio->id }}">

                                                        <div class="table-responsive">
                                                            <table class="table table-striped align-middle mb-3">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 70px;">Usar</th>
                                                                        <th>Articulo</th>
                                                                        <th style="width: 130px;">Cantidad</th>
                                                                        <th style="width: 130px;">Stock</th>
                                                                        <th style="width: 150px;">Carga</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($items as $item)
                                                                        @php
                                                                            $repuesto = $item['repuesto'];
                                                                            $defaultChecked = $item['automatico'] || $repuesto->obligatorio_servicio;
                                                                            $stockInsuficiente = $item['disponible'] < $item['cantidad'];
                                                                        @endphp
                                                                        <tr>
                                                                            <td>
                                                                                <input type="hidden" name="items[{{ $repuesto->id }}][usar]" value="0">
                                                                                <input class="form-check-input" type="checkbox"
                                                                                    name="items[{{ $repuesto->id }}][usar]" value="1"
                                                                                    @checked($defaultChecked)>
                                                                            </td>
                                                                            <td>
                                                                                <div class="fw-semibold">{{ $repuesto->articulo?->nombre }}</div>
                                                                                <small class="text-muted">
                                                                                    {{ $repuesto->articulo?->codigo_producto ?: 'Sin codigo' }}
                                                                                    {{ $repuesto->articulo?->unidadMedida ? '- ' . $repuesto->articulo->unidadMedida->nombre : '' }}
                                                                                </small>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" class="form-control"
                                                                                    name="items[{{ $repuesto->id }}][cantidad]"
                                                                                    value="{{ $item['cantidad'] }}" min="1" required>
                                                                            </td>
                                                                            <td>
                                                                                <span class="badge {{ $stockInsuficiente ? 'bg-light-warning' : 'bg-light-success' }}">
                                                                                    {{ $item['disponible'] }} disponible(s)
                                                                                </span>
                                                                            </td>
                                                                            <td>
                                                                                <span class="badge {{ $item['automatico'] ? 'bg-light-primary' : 'bg-light-secondary' }}">
                                                                                    {{ $item['automatico'] ? 'Automatica' : 'Manual' }}
                                                                                </span>
                                                                                @if ($repuesto->obligatorio_servicio)
                                                                                    <span class="badge bg-light-danger">Obligatorio</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                                                            <div class="form-check">
                                                                <input type="hidden" name="generar_pedido_faltantes" value="0">
                                                                <input class="form-check-input" type="checkbox" name="generar_pedido_faltantes"
                                                                    id="generar-pedido-kit-{{ $servicio->id }}" value="1"
                                                                    @checked($canCreatePedidos) @disabled(! $canCreatePedidos)>
                                                                <label class="form-check-label" for="generar-pedido-kit-{{ $servicio->id }}">
                                                                    Generar pedido de articulos por faltantes
                                                                </label>
                                                                @unless ($canCreatePedidos)
                                                                    <small class="text-muted d-block">Sin permiso para crear pedidos.</small>
                                                                @endunless
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="bi bi-box-arrow-down"></i> Cargar kit
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light-secondary mb-4">
                                Este vehiculo no tiene kits de servicio configurados en su ficha de repuestos.
                            </div>
                        @endif
                    @endunless

                    @unless ($ordenCerrada)
                    <form method="POST" action="{{ route('admin.ordenes-trabajo.articulos.store', $orden->id) }}" class="mb-3">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-2">
                                <label for="articulo-codigo-scan" class="form-label">Codigo / barras</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="articulo_codigo" id="articulo-codigo-scan" class="form-control"
                                        value="{{ old('articulo_codigo') }}" placeholder="Escanee o escriba codigo" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-12 col-md-{{ $canCreateArticulos ? '6' : '7' }}">
                                <label class="form-label">Articulo (*)</label>
                                <select name="articulo_id" id="articulo-id-select" class="form-select form-select-sm js-select2" data-icon-decorated="true" data-placeholder="Escriba para buscar articulo">
                                    <option value="">Seleccione articulo</option>
                                    @foreach ($articulos as $articulo)
                                        @php
                                            $articuloLabel = $articulo->nombre . ($articulo->codigo_producto ? ' - ' . $articulo->codigo_producto : '');
                                            $esMatafuego = $articleClassifier->isMatafuegoArticulo($articulo);
                                        @endphp
                                        <option value="{{ $articulo->id }}"
                                            data-codigo="{{ $articulo->codigo_producto }}"
                                            data-categoria="{{ $articulo->categoria?->nombre ?? '' }}"
                                            data-es-matafuego="{{ $esMatafuego ? '1' : '0' }}"
                                            @selected((string) old('articulo_id') === (string) $articulo->id)>
                                            {{ $articuloLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label">Cantidad (*)</label>
                                <input type="number" name="cantidad" class="form-control form-control-sm" value="{{ old('cantidad', 1) }}" min="1" required>
                            </div>
                            <div class="col-6 col-md-1">
                                <button type="submit" class="btn btn-sm btn-primary w-100 py-1" title="Agregar articulo" style="min-height: 34px;">
                                    <i class="bi bi-plus-circle"></i><span class="d-none d-xl-inline ms-1">Agregar</span>
                                </button>
                            </div>
                            @if ($canCreateArticulos)
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-info w-100" data-bs-toggle="collapse"
                                        data-bs-target="#quickArticleForm" aria-expanded="{{ $openQuickArticle ? 'true' : 'false' }}"
                                        aria-controls="quickArticleForm" title="Crear articulo rapido">
                                        <i class="bi bi-plus-square"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div id="matafuego-fields" class="row g-2 mt-2 {{ old('matafuego_numero') || old('matafuego_fecha_vencimiento') ? '' : 'd-none' }}">
                            <div class="col-12 col-md-3">
                                <label for="matafuego-numero" class="form-label">Nro. matafuego (*)</label>
                                <input type="text" name="matafuego_numero" id="matafuego-numero" class="form-control form-control-sm text-uppercase"
                                    value="{{ old('matafuego_numero') }}" maxlength="120" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="matafuego-vencimiento" class="form-label">Vencimiento (*)</label>
                                <input type="date" name="matafuego_fecha_vencimiento" id="matafuego-vencimiento" class="form-control form-control-sm"
                                    value="{{ old('matafuego_fecha_vencimiento') }}">
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-end">
                                <small class="text-muted">Estos datos se guardan con la orden para controlar vencimientos de matafuegos/extintores.</small>
                            </div>
                        </div>
                        <small id="articulo-codigo-feedback" class="text-muted d-block mt-1">
                            Preparado para lector: el escaneo busca por el codigo del articulo.
                        </small>
                    </form>
                    @endunless

                    @if ($canCreateArticulos && ! $ordenCerrada)
                        <div class="collapse {{ $openQuickArticle ? 'show' : '' }} mb-3" id="quickArticleForm">
                            <div class="border rounded p-2">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                    <div>
                                        <h5 class="mb-1">Articulo no cargado</h5>
                                        <small class="text-muted">
                                            Crea el articulo y lo agrega a esta orden sin descontar stock de inventario.
                                        </small>
                                    </div>
                                    <span class="badge bg-light-warning">Sin inventario</span>
                                </div>

                                <form method="POST" action="{{ route('admin.ordenes-trabajo.articulos.store', $orden->id) }}">
                                    @csrf
                                    <input type="hidden" name="crear_articulo" value="1">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-4 col-xl-3">
                                            <label for="quick-articulo-nombre" class="form-label">Nombre (*)</label>
                                            <input type="text" name="nombre" id="quick-articulo-nombre" class="form-control form-control-sm"
                                                value="{{ old('nombre') }}" maxlength="255" required>
                                        </div>
                                        <div class="col-6 col-md-2 col-xl-2">
                                            <label for="quick-articulo-codigo" class="form-label">Codigo</label>
                                            <input type="text" name="codigo_producto" id="quick-articulo-codigo" class="form-control form-control-sm"
                                                value="{{ old('codigo_producto') }}" maxlength="255">
                                        </div>
                                        <div class="col-12 col-md-3 col-xl-2">
                                            <label for="quick-articulo-categoria" class="form-label">Categoria (*)</label>
                                            <select name="categoria_id" id="quick-articulo-categoria" class="form-select form-select-sm" required>
                                                <option value="">Seleccione categoria</option>
                                                @foreach ($categorias as $categoria)
                                                    <option value="{{ $categoria->id }}" @selected((string) old('categoria_id') === (string) $categoria->id)>
                                                        {{ $categoria->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3 col-xl-2">
                                            <label for="quick-articulo-unidad" class="form-label">Unidad (*)</label>
                                            <select name="unidad_medida_id" id="quick-articulo-unidad" class="form-select form-select-sm" required>
                                                <option value="">Seleccione unidad</option>
                                                @foreach ($unidadesMedida as $unidadMedida)
                                                    <option value="{{ $unidadMedida->id }}" @selected((string) old('unidad_medida_id') === (string) $unidadMedida->id)>
                                                        {{ $unidadMedida->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-2 col-xl-1">
                                            <label for="quick-articulo-cantidad" class="form-label">Cantidad (*)</label>
                                            <input type="number" name="cantidad" id="quick-articulo-cantidad" class="form-control form-control-sm"
                                                value="{{ old('cantidad', 1) }}" min="1" required>
                                        </div>
                                        <div class="col-6 col-md-2 col-xl-1">
                                            <label for="quick-articulo-valor" class="form-label">Valor unit. (*)</label>
                                            <input type="text" name="valor_unitario" id="quick-articulo-valor" class="form-control form-control-sm"
                                                value="{{ old('valor_unitario', 0) }}" inputmode="decimal" required>
                                        </div>
                                        <div class="col-12 col-md-8 col-xl-3">
                                            <label for="quick-articulo-observaciones" class="form-label">Observaciones</label>
                                            <input type="text" name="observaciones" id="quick-articulo-observaciones" class="form-control form-control-sm"
                                                value="{{ old('observaciones') }}" maxlength="255">
                                        </div>
                                        <div class="col-12 col-md-4 col-xl-2">
                                            <button type="submit" class="btn btn-sm btn-info w-100">
                                                <i class="bi bi-plus-circle"></i> Crear y agregar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Movimiento</th>
                                    <th style="min-width: 280px; width: 38%;">Articulo</th>
                                    <th style="width: 120px;">Codigo</th>
                                    <th style="width: 130px;">Unidad</th>
                                    <th style="width: 110px;">Cantidad</th>
                                    <th style="width: 180px;">Matafuego</th>
                                    <th style="width: 140px;">Valor unit.</th>
                                    <th style="width: 140px;">Total</th>
                                    @unless ($ordenCerrada)
                                        <th class="text-end" style="width: 90px;">Acciones</th>
                                    @endunless
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orden->articulosUsados as $detalle)
                                    <tr>
                                        <td class="fw-semibold">{{ $detalle->numero_movimiento ?: \App\Models\OrdenTrabajoArticulo::numeroMovimientoPara($detalle) }}</td>
                                        <td>
                                            <div>{{ $detalle->articulo?->nombre }}</div>
                                            @unless ($detalle->inventario_descontado)
                                                <small class="badge bg-light-warning">Sin descuento de stock</small>
                                            @endunless
                                        </td>
                                        <td>{{ $detalle->articulo?->codigo_producto }}</td>
                                        <td>{{ $detalle->articulo?->unidadMedida?->nombre }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>
                                            @if ($detalle->matafuego_numero || $detalle->matafuego_fecha_vencimiento)
                                                <div class="fw-semibold">{{ $detalle->matafuego_numero ?: '-' }}</div>
                                                <small class="text-muted">
                                                    Vence: {{ $detalle->matafuego_fecha_vencimiento?->format('d/m/Y') ?? '-' }}
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>${{ number_format((float) $detalle->valor_unitario, 2, ',', '.') }}</td>
                                        <td>${{ number_format((float) $detalle->valor_unitario * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                        @unless ($ordenCerrada)
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.ordenes-trabajo.articulos.destroy', [$orden->id, $detalle->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endunless
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $ordenCerrada ? 8 : 9 }}" class="text-center text-muted py-4">
                                            No hay articulos cargados para esta orden.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('admin.partials.documentos-operativos', [
                'documentos' => $documentos ?? collect(),
                'documentableType' => 'orden_trabajo',
                'documentableId' => $orden->id,
                'editPermission' => 'ordenes-trabajo.editar',
            ])
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const codigoInput = document.getElementById('articulo-codigo-scan');
            const articuloSelect = document.getElementById('articulo-id-select');
            const feedback = document.getElementById('articulo-codigo-feedback');
            const matafuegoFields = document.getElementById('matafuego-fields');
            const matafuegoNumero = document.getElementById('matafuego-numero');
            const matafuegoVencimiento = document.getElementById('matafuego-vencimiento');

            if (!codigoInput || !articuloSelect) {
                return;
            }

            const setFeedback = function (message, className) {
                if (!feedback) {
                    return;
                }

                feedback.className = className + ' d-block mt-1';
                feedback.textContent = message;
            };

            const findOptionByCode = function (code) {
                const normalizedCode = normalizeBarcode(code);

                if (!normalizedCode) {
                    return null;
                }

                const isRopaEppCategoria = function (categoria) {
                    const normalized = (categoria || '').trim().toLowerCase();
                    return normalized.includes('ropa') || normalized.includes('epp') || normalized.includes('indument') || normalized.includes('protec');
                };

                const matches = Array.from(articuloSelect.options).filter(function (option) {
                    return normalizeBarcode(option.dataset.codigo || '') === normalizedCode;
                });

                if (matches.length === 0) {
                    return null;
                }

                const byVista = matches.filter(function (option) {
                    return !isRopaEppCategoria(option.dataset.categoria || '');
                });

                return {
                    option: (byVista[0] || matches[0] || null),
                    totalMatches: matches.length,
                    usedCategoryFilter: matches.length > 1 && byVista.length > 0,
                };
            };

            const normalizeBarcode = function (code) {
                return (code || '')
                    .toString()
                    .normalize('NFKC')
                    .trim()
                    .replace(/[^a-zA-Z0-9]/g, '')
                    .toLowerCase();
            };

            const selectByCode = function () {
                const match = findOptionByCode(codigoInput.value);
                const option = match?.option;

                if (!option) {
                    setFeedback('Codigo no encontrado en la lista. Puede presionar Agregar para buscarlo en el sistema.', 'text-warning');
                    return false;
                }

                articuloSelect.value = option.value;

                if (window.jQuery) {
                    window.jQuery(articuloSelect).trigger('change');
                } else {
                    articuloSelect.dispatchEvent(new Event('change'));
                }

                const selectedMessage = (match.totalMatches > 1 && match.usedCategoryFilter)
                    ? 'Articulo seleccionado por categoria: ' + option.text.trim()
                    : 'Articulo seleccionado: ' + option.text.trim();

                setFeedback(selectedMessage, 'text-success');
                updateMatafuegoFields();
                return true;
            };

            const updateMatafuegoFields = function () {
                const selectedOption = articuloSelect.options[articuloSelect.selectedIndex];
                const esMatafuego = selectedOption?.dataset?.esMatafuego === '1';

                matafuegoFields?.classList.toggle('d-none', !esMatafuego);

                if (matafuegoNumero) {
                    matafuegoNumero.required = esMatafuego;
                    if (!esMatafuego) {
                        matafuegoNumero.value = '';
                    }
                }

                if (matafuegoVencimiento) {
                    matafuegoVencimiento.required = esMatafuego;
                    if (!esMatafuego) {
                        matafuegoVencimiento.value = '';
                    }
                }
            };

            codigoInput.addEventListener('input', function () {
                if (codigoInput.value.trim().length >= 3) {
                    selectByCode();
                }
            });

            codigoInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                if (selectByCode()) {
                    codigoInput.closest('form')?.requestSubmit();
                }
            });

            articuloSelect.addEventListener('change', updateMatafuegoFields);

            if (window.jQuery) {
                window.jQuery(articuloSelect).on('select2:select select2:clear change', updateMatafuegoFields);
            }

            matafuegoNumero?.addEventListener('input', function () {
                this.value = this.value.toUpperCase();
            });

            updateMatafuegoFields();
        });
    </script>
@endpush
