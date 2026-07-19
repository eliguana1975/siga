@extends('layouts.admin')

@php
    $items = $resultado['items'];
    $displayItems = $resultado['paginated_items'];
    $isInventarioReport = in_array($reporte, ['control_categoria', 'bajo_stock', 'stock_deposito', 'valorizacion'], true);
    $isArticuloReport = in_array($reporte, ['sin_stock', 'ubicacion', 'sin_ubicacion'], true);
    $reportTitle = $reportes[$reporte] ?? 'Listados';
    $selectedCategoria = $categorias->firstWhere('id', (int) $filtros['categoria_id']);
    $selectedDeposito = $depositos->firstWhere('id', (int) $filtros['deposito_id']);
    $formatUbicacion = function ($articulo): string {
        return collect([
            $articulo?->pasillo ? 'P: ' . $articulo->pasillo : null,
            $articulo?->estanteria ? 'E: ' . $articulo->estanteria : null,
            $articulo?->casillero ? 'N: ' . $articulo->casillero : null,
        ])->filter()->implode(' / ') ?: '-';
    };
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Listados de articulos</h3>
                <p class="text-subtitle text-muted">
                    Consultas imprimibles para control de stock, ubicaciones y valorizacion.
                </p>
            </div>
            <button type="button" class="btn btn-outline-secondary" onclick="printReport()">
                <i class="bi bi-printer"></i> Imprimir listado
            </button>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Filtros de consulta</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.articulos.listado') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-4">
                                <label for="reporte" class="form-label">Listado</label>
                                <select name="reporte" id="reporte" class="form-select">
                                    @foreach ($reportes as $key => $label)
                                        <option value="{{ $key }}" @selected($reporte === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="categoria_id" class="form-label">Categoria</label>
                                <select name="categoria_id" id="categoria_id" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" @selected((string) $filtros['categoria_id'] === (string) $categoria->id)>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="deposito_id" class="form-label">Deposito</label>
                                <select name="deposito_id" id="deposito_id" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach ($depositos as $deposito)
                                        <option value="{{ $deposito->id }}" @selected((string) $filtros['deposito_id'] === (string) $deposito->id)>
                                            {{ $deposito->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="estado_item" class="form-label">Estado</label>
                                <select name="estado_item" id="estado_item" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="activo" @selected($filtros['estado_item'] === 'activo')>Activo</option>
                                    <option value="inactivo" @selected($filtros['estado_item'] === 'inactivo')>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $filtros['search'] }}" placeholder="Articulo, codigo o ubicacion">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel"></i> Consultar
                                </button>
                                <a href="{{ route('admin.articulos.listado') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="printableReport">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Consulta</p>
                                <h5 class="mb-0">{{ $reportTitle }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Registros</p>
                                <h3 class="mb-0">{{ number_format($resultado['total_items'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <p class="text-muted mb-1">Valor estimado</p>
                                <h3 class="mb-0">${{ number_format((float) $resultado['valor_total'], 2, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <h4 class="card-title mb-0">{{ $reportTitle }}</h4>
                            <small class="text-muted">Emitido el {{ now()->format('d/m/Y H:i') }}</small>
                        </div>
                        <span class="badge bg-light-info align-self-md-start">
                            {{ number_format($resultado['cantidad_total'], 0, ',', '.') }} unidad(es)
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @if ($isInventarioReport)
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Articulo</th>
                                            <th>Codigo</th>
                                            <th>Categoria</th>
                                            <th>Deposito</th>
                                            <th>Ubicacion</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Stock min/max</th>
                                            <th class="text-end">Precio unit.</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($displayItems as $item)
                                            @php
                                                $articulo = $item->articulo;
                                                $subtotal = (float) $item->cantidad * (float) $item->precio_compra_unidad;
                                                $faltante = max(0, (int) $item->stock_minimo - (int) $item->cantidad);
                                            @endphp
                                            <tr>
                                                <td>{{ $articulo?->nombre ?? 'N/A' }}</td>
                                                <td>{{ $articulo?->codigo_producto ?? '-' }}</td>
                                                <td>{{ $articulo?->categoria?->nombre ?? '-' }}</td>
                                                <td>{{ $item->deposito?->nombre ?? ($reporte === 'control_categoria' ? 'Todos' : '-') }}</td>
                                                <td>{{ $formatUbicacion($articulo) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((int) $item->cantidad, 0, ',', '.') }}
                                                    @if ($reporte === 'bajo_stock' && $faltante > 0)
                                                        <small class="text-warning d-block">Faltan {{ $faltante }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $item->stock_minimo }} / {{ $item->stock_maximo }}</td>
                                                <td class="text-end">${{ number_format((float) $item->precio_compra_unidad, 2, ',', '.') }}</td>
                                                <td class="text-end">${{ number_format($subtotal, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">No hay registros para la consulta seleccionada.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @elseif ($isArticuloReport)
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Articulo</th>
                                            <th>Codigo</th>
                                            <th>Categoria</th>
                                            <th>Unidad</th>
                                            <th>Ubicacion</th>
                                            <th>Estado</th>
                                            <th class="text-end">Stock total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($displayItems as $articulo)
                                            <tr>
                                                <td>{{ $articulo->nombre }}</td>
                                                <td>{{ $articulo->codigo_producto ?? '-' }}</td>
                                                <td>{{ $articulo->categoria?->nombre ?? '-' }}</td>
                                                <td>{{ $articulo->unidadMedida?->nombre ?? '-' }}</td>
                                                <td>{{ $formatUbicacion($articulo) }}</td>
                                                <td>
                                                    <span class="badge {{ $articulo->estado_item === 'activo' ? 'bg-light-success' : 'bg-light-secondary' }}">
                                                        {{ ucfirst($articulo->estado_item) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format((int) ($articulo->cantidad_total ?? 0), 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">No hay registros para la consulta seleccionada.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @else
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Orden</th>
                                            <th>Fecha</th>
                                            <th>Articulo</th>
                                            <th>Codigo</th>
                                            <th>Categoria</th>
                                            <th>Vehiculo</th>
                                            <th>Base</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Valor unit.</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($displayItems as $detalle)
                                            @php
                                                $subtotal = (float) $detalle->cantidad * (float) $detalle->valor_unitario;
                                            @endphp
                                            <tr>
                                                <td>#{{ $detalle->orden_trabajo_id }}</td>
                                                <td>{{ optional($detalle->ordenTrabajo?->fecha_orden)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $detalle->articulo?->nombre ?? '-' }}</td>
                                                <td>{{ $detalle->articulo?->codigo_producto ?? '-' }}</td>
                                                <td>{{ $detalle->articulo?->categoria?->nombre ?? '-' }}</td>
                                                <td>{{ $detalle->ordenTrabajo?->flota?->nro_interno ?? '-' }}</td>
                                                <td>{{ $detalle->ordenTrabajo?->base?->nombre ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) $detalle->cantidad, 0, ',', '.') }}</td>
                                                <td class="text-end">${{ number_format((float) $detalle->valor_unitario, 2, ',', '.') }}</td>
                                                <td class="text-end">${{ number_format($subtotal, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">No hay registros para la consulta seleccionada.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        @if ($displayItems->total() > 0)
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <small class="text-muted">
                                    Mostrando {{ $displayItems->firstItem() }} a {{ $displayItems->lastItem() }} de {{ $displayItems->total() }} registros
                                </small>
                                <div>{{ $displayItems->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div id="printableListReport" class="d-none">
                @if ($reporte === 'control_categoria')
                    <div class="stock-control-header">
                        <div class="print-company">
                            @if ($empresa['logo'])
                                <img src="{{ $empresa['logo'] }}" alt="{{ $empresa['nombre'] }}">
                            @endif
                            <div>
                                <h1>{{ $empresa['nombre'] }}</h1>
                                @if ($empresa['descripcion'])
                                    <p>{{ $empresa['descripcion'] }}</p>
                                @endif
                                <p>
                                    {{ collect([$empresa['direccion'], $empresa['localidad']])->filter()->implode(' - ') }}
                                    @if ($empresa['telefono'])
                                        | Tel: {{ $empresa['telefono'] }}
                                    @endif
                                    @if ($empresa['email'])
                                        | {{ $empresa['email'] }}
                                    @endif
                                    @if ($empresa['web'])
                                        | {{ $empresa['web'] }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <h2>Control de stock por categoria</h2>
                        <div class="stock-control-meta">
                            <div>
                                <span>Categoria</span>
                                <strong>{{ $selectedCategoria?->nombre ?? 'Todas' }}</strong>
                            </div>
                            <div>
                                <span>Deposito</span>
                                <strong>{{ $selectedDeposito?->nombre ?? 'Todos' }}</strong>
                            </div>
                            <div>
                                <span>Fecha</span>
                                <strong>{{ now()->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    <table class="stock-control-table">
                        <thead>
                            <tr>
                                <th>Articulo</th>
                                <th>Ubicacion</th>
                                <th class="text-end">Cantidad sistema</th>
                                <th>Cantidad estanteria</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $articulo = $item->articulo;
                                @endphp
                                <tr>
                                    <td>{{ $articulo?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $formatUbicacion($articulo) }}</td>
                                    <td class="text-end">{{ number_format((int) $item->cantidad, 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay registros para la consulta seleccionada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif ($reporte === 'ubicacion')
                    <div class="stock-control-header">
                        <div class="print-company">
                            @if ($empresa['logo'])
                                <img src="{{ $empresa['logo'] }}" alt="{{ $empresa['nombre'] }}">
                            @endif
                            <div>
                                <h1>{{ $empresa['nombre'] }}</h1>
                                @if ($empresa['descripcion'])
                                    <p>{{ $empresa['descripcion'] }}</p>
                                @endif
                                <p>
                                    {{ collect([$empresa['direccion'], $empresa['localidad']])->filter()->implode(' - ') }}
                                    @if ($empresa['telefono'])
                                        | Tel: {{ $empresa['telefono'] }}
                                    @endif
                                    @if ($empresa['email'])
                                        | {{ $empresa['email'] }}
                                    @endif
                                    @if ($empresa['web'])
                                        | {{ $empresa['web'] }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <h2>Articulos por ubicacion fisica</h2>
                        <div class="stock-control-meta">
                            <div>
                                <span>Categoria</span>
                                <strong>{{ $selectedCategoria?->nombre ?? 'Todas' }}</strong>
                            </div>
                            <div>
                                <span>Deposito</span>
                                <strong>{{ $selectedDeposito?->nombre ?? 'Todos' }}</strong>
                            </div>
                            <div>
                                <span>Fecha</span>
                                <strong>{{ now()->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    <table class="stock-control-table">
                        <thead>
                            <tr>
                                <th>Articulo</th>
                                <th>Codigo</th>
                                <th>Unidad</th>
                                <th>Ubicacion</th>
                                <th class="text-end">Stock total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $articulo)
                                <tr>
                                    <td>{{ $articulo->nombre }}</td>
                                    <td>{{ $articulo->codigo_producto ?? '-' }}</td>
                                    <td>{{ $articulo->unidadMedida?->nombre ?? '-' }}</td>
                                    <td>{{ $formatUbicacion($articulo) }}</td>
                                    <td class="text-end">{{ number_format((int) ($articulo->cantidad_total ?? 0), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay registros para la consulta seleccionada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <div class="stock-control-header">
                        <div class="print-company">
                            @if ($empresa['logo'])
                                <img src="{{ $empresa['logo'] }}" alt="{{ $empresa['nombre'] }}">
                            @endif
                            <div>
                                <h1>{{ $empresa['nombre'] }}</h1>
                                @if ($empresa['descripcion'])
                                    <p>{{ $empresa['descripcion'] }}</p>
                                @endif
                                <p>
                                    {{ collect([$empresa['direccion'], $empresa['localidad']])->filter()->implode(' - ') }}
                                    @if ($empresa['telefono'])
                                        | Tel: {{ $empresa['telefono'] }}
                                    @endif
                                    @if ($empresa['email'])
                                        | {{ $empresa['email'] }}
                                    @endif
                                    @if ($empresa['web'])
                                        | {{ $empresa['web'] }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <h2>{{ $reportTitle }}</h2>
                        <div class="stock-control-meta">
                            <div>
                                <span>Categoria</span>
                                <strong>{{ $selectedCategoria?->nombre ?? 'Todas' }}</strong>
                            </div>
                            <div>
                                <span>Deposito</span>
                                <strong>{{ $selectedDeposito?->nombre ?? 'Todos' }}</strong>
                            </div>
                            <div>
                                <span>Fecha</span>
                                <strong>{{ now()->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    @if ($isInventarioReport)
                        <table class="stock-control-table">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Codigo</th>
                                    <th>Categoria</th>
                                    <th>Deposito</th>
                                    <th>Ubicacion</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Stock min/max</th>
                                    <th class="text-end">Precio unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $articulo = $item->articulo;
                                        $subtotal = (float) $item->cantidad * (float) $item->precio_compra_unidad;
                                    @endphp
                                    <tr>
                                        <td>{{ $articulo?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $articulo?->codigo_producto ?? '-' }}</td>
                                        <td>{{ $articulo?->categoria?->nombre ?? '-' }}</td>
                                        <td>{{ $item->deposito?->nombre ?? '-' }}</td>
                                        <td>{{ $formatUbicacion($articulo) }}</td>
                                        <td class="text-end">{{ number_format((int) $item->cantidad, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ $item->stock_minimo }} / {{ $item->stock_maximo }}</td>
                                        <td class="text-end">${{ number_format((float) $item->precio_compra_unidad, 2, ',', '.') }}</td>
                                        <td class="text-end">${{ number_format($subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No hay registros para la consulta seleccionada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif ($isArticuloReport)
                        <table class="stock-control-table">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Codigo</th>
                                    <th>Categoria</th>
                                    <th>Unidad</th>
                                    <th>Ubicacion</th>
                                    <th>Estado</th>
                                    <th class="text-end">Stock total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $articulo)
                                    <tr>
                                        <td>{{ $articulo->nombre }}</td>
                                        <td>{{ $articulo->codigo_producto ?? '-' }}</td>
                                        <td>{{ $articulo->categoria?->nombre ?? '-' }}</td>
                                        <td>{{ $articulo->unidadMedida?->nombre ?? '-' }}</td>
                                        <td>{{ $formatUbicacion($articulo) }}</td>
                                        <td>{{ ucfirst($articulo->estado_item) }}</td>
                                        <td class="text-end">{{ number_format((int) ($articulo->cantidad_total ?? 0), 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay registros para la consulta seleccionada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <table class="stock-control-table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Fecha</th>
                                    <th>Articulo</th>
                                    <th>Codigo</th>
                                    <th>Categoria</th>
                                    <th>Vehiculo</th>
                                    <th>Base</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Valor unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $detalle)
                                    @php
                                        $subtotal = (float) $detalle->cantidad * (float) $detalle->valor_unitario;
                                    @endphp
                                    <tr>
                                        <td>#{{ $detalle->orden_trabajo_id }}</td>
                                        <td>{{ optional($detalle->ordenTrabajo?->fecha_orden)->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $detalle->articulo?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->articulo?->codigo_producto ?? '-' }}</td>
                                        <td>{{ $detalle->articulo?->categoria?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->ordenTrabajo?->flota?->nro_interno ?? '-' }}</td>
                                        <td>{{ $detalle->ordenTrabajo?->base?->nombre ?? '-' }}</td>
                                        <td class="text-end">{{ number_format((int) $detalle->cantidad, 0, ',', '.') }}</td>
                                        <td class="text-end">${{ number_format((float) $detalle->valor_unitario, 2, ',', '.') }}</td>
                                        <td class="text-end">${{ number_format($subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No hay registros para la consulta seleccionada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function printReport() {
            const content = document.getElementById('printableListReport');

            if (!content) {
                return;
            }

            const printWindow = window.open('', '_blank');
            const style = `
                <style>
                    body{font-family:Arial,Helvetica,sans-serif;color:#1f2937;padding:20px;}
                    h3,h4,h5{margin:0 0 6px;}
                    .card{border:0;margin-bottom:16px;}
                    .card-body,.card-header{padding:0 0 10px;}
                    .row{display:block;}
                    .badge{font-weight:700;}
                    .text-muted{color:#6b7280;}
                    table{width:100%;border-collapse:collapse;font-size:11px;}
                    th,td{border:1px solid #d1d5db;padding:7px;text-align:left;vertical-align:top;}
                    th{background:#f3f4f6;font-weight:700;}
                    .text-end{text-align:right;}
                    .d-block{display:block;}
                    .table-responsive{overflow:visible;}
                    .btn{display:none;}
                    .stock-control-header{border-bottom:2px solid #111827;margin-bottom:10px;padding-bottom:8px;}
                    .print-company{display:flex;align-items:flex-start;gap:12px;margin-bottom:10px;}
                    .print-company img{width:58px;height:58px;object-fit:contain;}
                    .print-company h1{font-size:16px;margin:0 0 3px;text-align:left;}
                    .print-company p{font-size:10px;line-height:1.35;margin:0 0 2px;color:#374151;}
                    .stock-control-header h2{font-size:18px;margin:0 0 10px;text-align:center;}
                    .stock-control-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
                    .stock-control-meta div{border:1px solid #d1d5db;padding:7px 8px;}
                    .stock-control-meta span{display:block;color:#111827;font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;}
                    .stock-control-meta strong{font-size:12px;}
                    .stock-control-table th:nth-child(1){width:44%;}
                    .stock-control-table th:nth-child(2){width:22%;}
                    .stock-control-table th:nth-child(3){width:14%;}
                    .stock-control-table th:nth-child(4){width:20%;}
                    .stock-control-table td{height:24px;}
                </style>
            `;
            printWindow.document.write('<html><head><title>{{ $reportTitle }}</title>' + style + '</head><body>');
            printWindow.document.write(content.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }
    </script>
@endpush
