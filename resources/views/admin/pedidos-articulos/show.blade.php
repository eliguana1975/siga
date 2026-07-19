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
                <h3>Pedido de articulos #{{ $pedido->id }}</h3>
                <p class="text-subtitle text-muted">Detalle general del pedido.</p>
            </div>
            <a href="{{ route('admin.pedidos-articulos.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            @if (($pedido->compras_count ?? $pedido->compras->count()) > 0)
                <button type="button" class="btn btn-secondary" disabled>
                    <i class="bi bi-cart-check"></i> Orden de compra generada
                </button>
            @elseif (! in_array($pedido->estado, ['cancelado', 'ingresado'], true))
                <a href="{{ route('admin.ordenes-compra.create', ['pedido_articulo_id' => $pedido->id]) }}" class="btn btn-primary">
                    <i class="bi bi-cart-plus"></i> Generar orden de compra
                </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 220px;">Deposito</th>
                                    <td>{{ $pedido->deposito?->nombre ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Usuario</th>
                                    <td>{{ $pedido->usuario?->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha</th>
                                    <td>{{ $pedido->fecha_pedido?->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Estado</th>
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
                                </tr>
                                @if ($pedido->hasStockBypassException())
                                    <tr>
                                        <th>Auditoria</th>
                                        <td>Pedido creado con excepcion de stock por jefe de compras.</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Notas</th>
                                    <td>{{ $pedido->notasSinMarcadores() ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Detalle de articulos</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Unidad</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pedido->detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No hay detalles cargados para este pedido.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
