@extends('layouts.admin')

@php
    $ubicacionLabel = function ($articulo) {
        if (! $articulo) {
            return 'Sin ubicacion';
        }

        return collect([
            $articulo->pasillo ? 'P ' . $articulo->pasillo : null,
            $articulo->estanteria ? 'E ' . $articulo->estanteria : null,
            $articulo->casillero ? 'C ' . $articulo->casillero : null,
        ])->filter()->implode(' / ') ?: 'Sin detalle';
    };
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Articulos con bajo stock</h3>
                <p class="text-subtitle text-muted">Inventarios con cantidad disponible en stock minimo o por debajo.</p>
            </div>
            <a href="{{ route('admin.inventarios.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de articulos con bajo stock</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.inventarios.bajo-stock') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar articulo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Articulo, codigo, deposito o ubicacion">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.inventarios.bajo-stock') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $inventarios->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    <th>Articulo</th>
                                    <th>Deposito</th>
                                    <th>Ubicacion fisica</th>
                                    <th>Cantidad</th>
                                    <th>Stock min/max</th>
                                    <th>Precio unidad</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventarios as $inventario)
                                    <tr>
                                        <td>{{ $inventarios->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $inventario->articulo->nombre ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $inventario->articulo->codigo_producto ?? 'Sin codigo' }}</small>
                                        </td>
                                        <td>{{ $inventario->deposito->nombre ?? 'N/A' }}</td>
                                        <td>{{ $ubicacionLabel($inventario->articulo) }}</td>
                                        <td>{{ number_format((int) $inventario->cantidad, 0, ',', '.') }}</td>
                                        <td>{{ $inventario->stock_minimo }} / {{ $inventario->stock_maximo }}</td>
                                        <td>{{ $inventario->precio_compra_unidad !== null ? '$' . number_format((float) $inventario->precio_compra_unidad, 2, ',', '.') : 'N/A' }}</td>
                                        <td>
                                            @if ((int) $inventario->cantidad === 0)
                                                <span class="badge bg-light-danger">Sin stock</span>
                                            @else
                                                <span class="badge bg-light-warning">Bajo stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No hay articulos con bajo stock.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($inventarios->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $inventarios->firstItem() }} a {{ $inventarios->lastItem() }} de {{ $inventarios->total() }} registros
                            </small>
                            <div>
                                {{ $inventarios->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
