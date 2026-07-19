@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Ingreso de articulos #{{ $entrada->id }}</h3>
                <p class="text-subtitle text-muted">Detalle general del ingreso registrado.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.entradas.edit', $entrada->id) }}" class="btn btn-success">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
                <a href="{{ route('admin.entradas.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
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
                                    <th style="width: 220px;">Fecha</th>
                                    <td>{{ $entrada->fecha_entrada?->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Nro. comprobante proveedor</th>
                                    <td>{{ $entrada->nro_comprobante_proveedor ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Deposito</th>
                                    <td>{{ $entrada->deposito?->nombre ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Proveedor</th>
                                    <td>{{ $entrada->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                </tr>
                                <tr>
                                    <th>Usuario</th>
                                    <td>{{ $entrada->usuario?->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Orden de compra</th>
                                    <td>
                                        @if ($entrada->compra)
                                            <a href="{{ route('admin.ordenes-compra.show', $entrada->compra->id) }}">Orden #{{ $entrada->compra->id }}</a>
                                        @else
                                            {{ $entrada->nro_orden_compra ?: '-' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td>${{ number_format((float) $entrada->total_entrada, 2, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Observaciones</th>
                                    <td>{{ $entrada->observaciones ?: '-' }}</td>
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
                                    <th>Precio unidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entrada->detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>${{ number_format((float) $detalle->precio_compra_unidad, 2, ',', '.') }}</td>
                                        <td>${{ number_format((float) $detalle->precio_compra_unidad * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay articulos cargados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th>${{ number_format((float) $entrada->total_entrada, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if ($entrada->compra)
                @php
                    $pendientes = $entrada->compra->detalles->map(function ($detalle) {
                        $ingresado = (int) $detalle->detallesEntrada->sum('cantidad');
                        $pendiente = max(0, (int) $detalle->cantidad - $ingresado);

                        return [
                            'articulo' => $detalle->articulo?->nombre ?? 'N/A',
                            'unidad' => $detalle->articulo?->unidadMedida?->nombre ?? '-',
                            'proveedor' => $detalle->proveedor?->nombre ?? 'Sin proveedor',
                            'ordenado' => (int) $detalle->cantidad,
                            'ingresado' => $ingresado,
                            'pendiente' => $pendiente,
                        ];
                    })->filter(fn ($detalle) => $detalle['pendiente'] > 0)->values();
                @endphp

                @if ($pendientes->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Pendientes de la orden</h4>
                        </div>
                        <div class="card-body">
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendientes as $pendiente)
                                            <tr>
                                                <td>{{ $pendiente['articulo'] }}</td>
                                                <td>{{ $pendiente['proveedor'] }}</td>
                                                <td>{{ $pendiente['unidad'] }}</td>
                                                <td>{{ $pendiente['ordenado'] }}</td>
                                                <td>{{ $pendiente['ingresado'] }}</td>
                                                <td><span class="badge bg-light-warning">{{ $pendiente['pendiente'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @include('admin.partials.documentos-operativos', [
                'documentos' => $documentos,
                'documentableType' => 'entrada',
                'documentableId' => $entrada->id,
                'editPermission' => 'entradas.editar',
            ])
        </section>
    </div>
@endsection
