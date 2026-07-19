@extends('layouts.admin')

@php
    $estadoLabels = [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'ingresado' => 'Ingresado',
        'cancelado' => 'Cancelado',
    ];

    $estadoBadges = [
        'pendiente' => 'bg-light-warning',
        'confirmado' => 'bg-light-success',
        'ingresado' => 'bg-light-primary',
        'cancelado' => 'bg-light-secondary',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Pedidos de articulos</h3>
                <p class="text-subtitle text-muted">Consulta los pedidos registrados por deposito, usuario y estado.</p>
            </div>
            @can('pedidos-articulos.crear')
                <a href="{{ route('admin.pedidos-articulos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo pedido
                </a>
            @endcan
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

            @if (session('info'))
                <div class="alert alert-info alert-dismissible show fade">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                    <div>
                        <h4 class="card-title mb-0">Articulos en nivel de pedido</h4>
                        <small class="text-muted">
                            Selecciona que articulos queres agregar al pedido pendiente. La distribucion por pañol se realiza al ingresar stock.
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    @unless ($pedidosAutomaticosActivos)
                        <div class="alert alert-info">
                            Active pedidos automaticos en ajustes para ver articulos sugeridos.
                        </div>
                    @endunless

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Stock actual total</th>
                                    <th>Nivel pedido</th>
                                    <th>Cantidad sugerida</th>
                                    <th class="text-end" style="width: 140px;">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sugeridos as $sugerido)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $sugerido['articulo']?->nombre ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $sugerido['articulo']?->codigo_producto ?? '-' }}</small>
                                        </td>
                                        <td>
                                            {{ $sugerido['stock_actual'] }}
                                            @unless ($sugerido['tiene_inventario'])
                                                <small class="d-block text-muted">Sin registro de inventario</small>
                                            @endunless
                                        </td>
                                        <td>{{ $sugerido['stock_pedido'] }}</td>
                                        <td>{{ $sugerido['cantidad_sugerida'] }}</td>
                                        <td class="text-end">
                                            @can('pedidos-articulos.crear')
                                                <form method="POST" action="{{ route('admin.pedidos-articulos.generar-sugeridos') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="articulo_id" value="{{ $sugerido['articulo_id'] }}">
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Agregar al pedido">
                                                        <i class="bi bi-cart-plus"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay articulos pendientes de pedir.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de pedidos registrados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.pedidos-articulos.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar pedido</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Deposito, usuario, estado o notas">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.pedidos-articulos.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'pedidos_articulos')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'pedidos_articulos')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'pedidos_articulos')">
                                <i class="bi bi-file-earmark-pdf"></i> Crear PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="printTable('datatable')">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Deposito</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Articulos</th>
                                    <th>Estado</th>
                                    <th>Notas</th>
                                    <th class="text-end" style="width: 160px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pedidos as $pedido)
                                    <tr>
                                        <td>{{ $pedidos->firstItem() + $loop->index }}</td>
                                        <td>{{ $pedido->deposito?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $pedido->usuario?->name ?? 'N/A' }}</td>
                                        <td>{{ $pedido->fecha_pedido?->format('d/m/Y') }}</td>
                                        <td>{{ $pedido->detalles_count }}</td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$pedido->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$pedido->estado] ?? ucfirst((string) $pedido->estado) }}
                                            </span>
                                            @if ($pedido->hasStockBypassException())
                                                <span class="badge bg-light-warning text-dark ms-1" title="Pedido creado con excepcion de stock por jefe de compras">
                                                    Excepcion stock (jefe compras)
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $pedido->notasSinMarcadores() ?: '-' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.pedidos-articulos.show', $pedido->id) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('pedidos-articulos.editar')
                                                <a href="{{ route('admin.pedidos-articulos.edit', $pedido->id) }}" class="btn btn-sm btn-success" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endcan
                                            @can('pedidos-articulos.eliminar')
                                                <form action="{{ route('admin.pedidos-articulos.destroy', $pedido->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar el pedido #{{ $pedido->id }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No hay pedidos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($pedidos->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $pedidos->firstItem() }} a {{ $pedidos->lastItem() }} de {{ $pedidos->total() }} registros
                            </small>
                            <div>{{ $pedidos->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
