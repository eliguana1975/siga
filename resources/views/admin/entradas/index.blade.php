@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Ingresos de articulos</h3>
                <p class="text-subtitle text-muted">Consulta los ingresos registrados por deposito, proveedor y usuario.</p>
            </div>
            @can('entradas.crear')
                <a href="{{ route('admin.entradas.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo ingreso
                </a>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de ingresos registrados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.entradas.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar ingreso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Orden, comprobante, proveedor, deposito, usuario u observaciones">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.entradas.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @include('admin.partials.saved-filters', [
                        'filterKey' => 'entradas',
                        'filterRoute' => 'admin.entradas.index',
                    ])

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $entradas->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Fecha</th>
                                    <th>Comprobante</th>
                                    <th>Orden compra</th>
                                    <th>Deposito</th>
                                    <th>Proveedor</th>
                                    <th>Usuario</th>
                                    <th>Articulos</th>
                                    <th>Total</th>
                                    <th class="text-end" style="width: 180px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entradas as $entrada)
                                    <tr>
                                        <td>{{ $entradas->firstItem() + $loop->index }}</td>
                                        <td>{{ $entrada->fecha_entrada?->format('d/m/Y') }}</td>
                                        <td>{{ $entrada->nro_comprobante_proveedor ?: '-' }}</td>
                                        <td>
                                            @if ($entrada->compra)
                                                <a href="{{ route('admin.ordenes-compra.show', $entrada->compra->id) }}">#{{ $entrada->compra->id }}</a>
                                            @else
                                                {{ $entrada->nro_orden_compra ?: '-' }}
                                            @endif
                                        </td>
                                        <td>{{ $entrada->deposito?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $entrada->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                        <td>{{ $entrada->usuario?->name ?? 'N/A' }}</td>
                                        <td>{{ $entrada->detalles->count() }}</td>
                                        <td>${{ number_format((float) $entrada->total_entrada, 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.entradas.show', $entrada->id) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('entradas.editar')
                                                <a href="{{ route('admin.entradas.edit', $entrada->id) }}" class="btn btn-sm btn-success" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endcan
                                            @can('entradas.eliminar')
                                                <form action="{{ route('admin.entradas.destroy', $entrada->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar el ingreso #{{ $entrada->id }}? Se descontara del inventario.');">
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
                                        <td colspan="10" class="text-center text-muted py-4">No hay ingresos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($entradas->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $entradas->firstItem() }} a {{ $entradas->lastItem() }} de {{ $entradas->total() }} registros
                            </small>
                            <div>
                                {{ $entradas->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
