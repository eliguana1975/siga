@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Pendientes de entrega</h3>
                <p class="text-subtitle text-muted">Consulta articulos faltantes por orden y registra su ingreso al stock.</p>
            </div>
            <a href="{{ route('admin.entradas.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Ingresos
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ordenes con articulos pendientes</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.entradas.pendientes') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar pendiente</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Orden, proveedor, deposito, articulo o codigo">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.entradas.pendientes') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @forelse ($ordenes as $orden)
                        @php
                            $compra = $orden['compra'];
                        @endphp
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <a href="{{ route('admin.ordenes-compra.show', $compra->id) }}">
                                            Orden #{{ $compra->id }}
                                        </a>
                                    </h5>
                                    <div class="text-muted small">
                                        {{ $compra->proveedor?->nombre ?? 'Sin proveedor' }} ·
                                        {{ $compra->deposito?->nombre ?? 'Sin deposito' }} ·
                                        {{ $compra->fecha_compra?->format('d/m/Y') }}
                                    </div>
                                    <div class="small mt-1 {{ $orden['tiene_ingresos'] ? 'text-warning' : 'text-muted' }}">
                                        @if ($orden['tiene_ingresos'])
                                            Ya se recibieron {{ $orden['total_ingresado'] }} unidad(es); se muestran los articulos que aun faltan.
                                        @else
                                            Todavia no se registro ningun ingreso; todos los articulos de la orden estan faltando.
                                        @endif
                                    </div>
                                </div>
                                <div class="text-md-end d-flex flex-column align-items-start align-items-md-end gap-1">
                                    @if ($orden['tiene_ingresos'])
                                        <span class="badge bg-light-info">Recepcion parcial</span>
                                    @else
                                        <span class="badge bg-light-secondary">Sin ingresos</span>
                                    @endif
                                    <span class="badge bg-light-warning">Pendientes: {{ $orden['total_pendiente'] }}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Articulo</th>
                                            <th>Proveedor</th>
                                            <th>Unidad</th>
                                            <th>Ordenado</th>
                                            <th>Ingresado</th>
                                            <th>Pendiente</th>
                                            <th>Precio</th>
                                            <th style="width: 360px;">Registrar llegada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orden['pendientes'] as $detalle)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $detalle['articulo'] }}</div>
                                                    <small class="text-muted">{{ $detalle['codigo'] }}</small>
                                                </td>
                                                <td>{{ $detalle['proveedor'] }}</td>
                                                <td>{{ $detalle['unidad'] }}</td>
                                                <td>{{ $detalle['ordenado'] }}</td>
                                                <td>{{ $detalle['ingresado'] }}</td>
                                                <td><span class="badge bg-light-warning">{{ $detalle['pendiente'] }}</span></td>
                                                <td>${{ number_format($detalle['precio'], 2, ',', '.') }}</td>
                                                <td>
                                                    @can('entradas.crear')
                                                        <form method="POST" action="{{ route('admin.entradas.pendientes.store') }}"
                                                            class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center">
                                                            @csrf
                                                            <input type="hidden" name="compra_detalle_id" value="{{ $detalle['id'] }}">
                                                            <input type="hidden" name="fecha_entrada" value="{{ now()->format('Y-m-d') }}">
                                                            <input type="number" name="cantidad" class="form-control form-control-sm"
                                                                value="{{ $detalle['pendiente'] }}" min="1" max="{{ $detalle['pendiente'] }}" step="1" required>
                                                            @if ($detalle['es_cubierta'] && ! $detalle['cubiertas_existentes'])
                                                                <div class="d-flex flex-column flex-md-row gap-2">
                                                                    <select name="cubiertas_tiene_numeracion" class="form-select form-select-sm">
                                                                        <option value="0">Sin numeracion: iniciar 001</option>
                                                                        <option value="1">Tiene numeracion</option>
                                                                    </select>
                                                                    <input type="number" name="cubiertas_numero_inicial" class="form-control form-control-sm"
                                                                        min="1" step="1" placeholder="Nro inicial">
                                                                </div>
                                                            @endif
                                                            <input type="text" name="nro_comprobante_proveedor" class="form-control form-control-sm"
                                                                placeholder="Comprobante">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="bi bi-box-arrow-in-down"></i> Ingresar
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">Sin permiso para ingresar stock</span>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No hay articulos pendientes en ordenes aprobadas.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
