@extends('layouts.admin')

@php
    $estadoLabels = [
        'pendiente' => 'Pendiente',
        'aprobada' => 'Aprobada',
        'recibido' => 'Recibido',
        'cancelado' => 'Cancelado',
    ];

    $estadoBadges = [
        'pendiente' => 'bg-light-warning',
        'aprobada' => 'bg-light-primary',
        'recibido' => 'bg-light-success',
        'cancelado' => 'bg-light-secondary',
    ];

    $plazosPago = [
        '' => 'Sin plazo',
        '0' => 'A la vista',
        '15' => '15 dias',
        '30' => '30 dias',
        '45' => '45 dias',
        '60' => '60 dias',
        '90' => '90 dias',
        '120' => '120 dias',
        '30-60' => '30 - 60 dias',
        '30-60-90' => '30 - 60 - 90 dias',
        '30-60-90-120' => '30 - 60 - 90 - 120 dias',
    ];

    $impuestosPago = $impuestosPago ?? [];
    $proveedoresImpuestos = collect([$compra->proveedor])
        ->merge($compra->detalles->pluck('proveedor'))
        ->filter()
        ->unique('id')
        ->map(fn ($proveedor) => [
            'proveedor' => $proveedor,
            'impuestos' => $proveedor->impuestosActivos(),
        ])
        ->filter(fn ($item) => count($item['impuestos']) > 0)
        ->values();
    $importeImpuestosProveedor = $compra->importeImpuestosProveedor();
    $totalConImpuestos = $compra->totalConImpuestos();
    $saldoConImpuestos = $compra->saldoPendienteConImpuestos();
@endphp

@push('styles')
    <style>
        .order-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem 1rem;
        }

        .order-summary-item {
            min-width: 0;
        }

        .order-summary-label {
            display: block;
            margin-bottom: .2rem;
            color: var(--bs-secondary-color);
            font-size: .78rem;
            font-weight: 600;
        }

        .order-summary-value {
            min-height: 1.45rem;
            overflow-wrap: anywhere;
            font-weight: 600;
        }

        .payment-section {
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            padding: 1rem;
        }

        .payment-section-title {
            color: var(--bs-secondary-color);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .02em;
            margin-bottom: .75rem;
            text-transform: uppercase;
        }

        .payment-due-preview {
            min-height: 2.4rem;
        }

        .payment-tax-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 120px 130px;
            gap: .75rem;
            align-items: center;
        }

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

        @media (max-width: 767.98px) {
            .payment-tax-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .order-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .order-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Orden de compra #{{ $compra->id }}</h3>
                <p class="text-subtitle text-muted">Detalle general de la orden de compra.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="order-summary-grid">
                        <div class="order-summary-item">
                            <span class="order-summary-label">Deposito</span>
                            <div class="order-summary-value">{{ $compra->deposito?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Proveedor</span>
                            <div class="order-summary-value">{{ $compra->proveedorResumen() }}</div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Pedido de articulos</span>
                            <div class="order-summary-value">
                                @if ($compra->pedidoArticulo)
                                    <a href="{{ route('admin.pedidos-articulos.show', $compra->pedidoArticulo->id) }}">
                                        Pedido #{{ $compra->pedidoArticulo->id }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Usuario</span>
                            <div class="order-summary-value">{{ $compra->usuario?->name ?? 'N/A' }}</div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Fecha</span>
                            <div class="order-summary-value">{{ $compra->fecha_compra?->format('d/m/Y') }}</div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Total con impuestos</span>
                            <div class="order-summary-value">${{ number_format($totalConImpuestos, 2, ',', '.') }}</div>
                            <small class="text-muted">
                                Base ${{ number_format((float) $compra->total_compra, 2, ',', '.') }} /
                                Imp. ${{ number_format($importeImpuestosProveedor, 2, ',', '.') }}
                            </small>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Forma de pago</span>
                            <div class="order-summary-value">{{ $compra->formaPagoLabel() }}</div>
                            @if ($compra->datos_pago)
                                <small class="text-muted">{{ $compra->datos_pago }}</small>
                            @endif
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Pagado</span>
                            <div class="order-summary-value">${{ number_format($compra->totalPagado(), 2, ',', '.') }}</div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Saldo</span>
                            <div class="order-summary-value">${{ number_format($saldoConImpuestos, 2, ',', '.') }}</div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Estado</span>
                            <div class="order-summary-value">
                                <span class="badge {{ $estadoBadges[$compra->estado] ?? 'bg-light-secondary' }}">
                                    {{ $estadoLabels[$compra->estado] ?? ucfirst((string) $compra->estado) }}
                                </span>
                            </div>
                        </div>
                        <div class="order-summary-item">
                            <span class="order-summary-label">Notas</span>
                            <div class="order-summary-value">{{ $compra->notas ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Impuestos de proveedores</h4>
                </div>
                <div class="card-body">
                    <div class="provider-tax-summary">
                        @forelse ($proveedoresImpuestos as $item)
                            <div class="provider-tax-item">
                                <div class="fw-semibold mb-1">{{ $item['proveedor']->nombre }}</div>
                                <div>
                                    @foreach ($item['impuestos'] as $impuesto)
                                        <span class="provider-tax-chip">
                                            <strong>{{ $impuesto['nombre'] }}</strong>
                                            <span>{{ number_format((float) ($impuesto['porcentaje'] ?? 0), 2, ',', '.') }}%</span>
                                            @if (! empty($impuesto['descripcion']))
                                                <small class="text-muted">{{ $impuesto['descripcion'] }}</small>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No hay impuestos activos cargados para los proveedores de esta orden.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @include('admin.partials.documentos-operativos', [
                'documentos' => $documentos ?? collect(),
                'documentableType' => 'compra',
                'documentableId' => $compra->id,
                'editPermission' => 'ordenes-compra.editar',
            ])

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Pagos registrados</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Forma</th>
                                    <th>Importe</th>
                                    <th>Cheque / Banco</th>
                                    <th>Comprobante</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compra->pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $pago->formaPagoLabel() }}</td>
                                        <td>
                                            ${{ number_format((float) $pago->importe, 2, ',', '.') }}
                                            @if ($pago->importe_base !== null)
                                                <small class="d-block text-muted">Base ${{ number_format((float) $pago->importe_base, 2, ',', '.') }}</small>
                                            @endif
                                            @if ((float) $pago->importe_impuestos > 0)
                                                <small class="d-block text-muted">Imp. ${{ number_format((float) $pago->importe_impuestos, 2, ',', '.') }}</small>
                                                @foreach ($pago->impuestosAplicados() as $impuesto)
                                                    <small class="d-block text-muted">
                                                        {{ $impuesto['nombre'] ?? 'Impuesto' }} {{ number_format((float) ($impuesto['porcentaje'] ?? 0), 2, ',', '.') }}%:
                                                        ${{ number_format((float) ($impuesto['importe'] ?? 0), 2, ',', '.') }}
                                                    </small>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @forelse ($pago->numerosCheques() as $index => $numeroCheque)
                                                <span class="d-block">{{ $index + 1 }}. {{ $numeroCheque }}</span>
                                            @empty
                                                {{ $pago->nro_operacion_cheque ?: '-' }}
                                            @endforelse
                                            @if ($pago->banco)
                                                <small class="d-block text-muted">{{ $pago->banco }}</small>
                                            @endif
                                            @if ($pago->plazo_pago !== null && $pago->plazo_pago !== '')
                                                <small class="d-block text-muted">
                                                    Plazo {{ $pago->plazo_pago === '0' ? 'a la vista' : str_replace('-', ' - ', $pago->plazo_pago) . ' dias' }}
                                                </small>
                                            @endif
                                            @if (! empty($pago->vencimientosPago()))
                                                @foreach ($pago->vencimientosPago() as $vencimiento)
                                                    <small class="d-block text-muted">Vence {{ \Carbon\Carbon::parse($vencimiento)->format('d/m/Y') }}</small>
                                                @endforeach
                                            @elseif ($pago->fecha_vencimiento_cheque)
                                                <small class="d-block text-muted">Vence {{ $pago->fecha_vencimiento_cheque->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $pago->nro_comprobante_pago ?: '-' }}
                                            @if ($pago->nro_transferencia)
                                                <small class="d-block text-muted">Transf. {{ $pago->nro_transferencia }}</small>
                                            @endif
                                            @if ($pago->nro_recibo)
                                                <small class="d-block text-muted">Recibo {{ $pago->nro_recibo }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $pago->usuario?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No hay pagos registrados para esta orden.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total pagado</th>
                                    <th>${{ number_format($compra->totalPagado(), 2, ',', '.') }}</th>
                                    <th colspan="3" class="text-muted">Saldo pendiente con impuestos: ${{ number_format($saldoConImpuestos, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
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
                                    <th>Proveedor</th>
                                    <th>Unidad</th>
                                    <th>Cantidad</th>
                                    <th>Ingresado</th>
                                    <th>Pendiente</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compra->detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $detalle->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                        <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        @php
                                            $cantidadIngresada = $detalle->detallesEntrada->sum('cantidad');
                                            $cantidadPendiente = max(0, (int) $detalle->cantidad - (int) $cantidadIngresada);
                                        @endphp
                                        <td>{{ $cantidadIngresada }}</td>
                                        <td>
                                            @if ($cantidadPendiente > 0)
                                                <span class="badge bg-light-warning">{{ $cantidadPendiente }}</span>
                                            @else
                                                <span class="badge bg-light-success">Completo</span>
                                            @endif
                                        </td>
                                        <td>${{ number_format((float) $detalle->precio_compra_unidad, 2, ',', '.') }}</td>
                                        <td>${{ number_format((float) $detalle->precio_compra_unidad * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No hay detalles cargados para esta orden.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end">Base</th>
                                    <th>${{ number_format((float) $compra->total_compra, 2, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end">Impuestos</th>
                                    <th>${{ number_format($importeImpuestosProveedor, 2, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end">Total con impuestos</th>
                                    <th>${{ number_format($totalConImpuestos, 2, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-end text-muted">
                                        Los impuestos se calculan segun el proveedor asignado a cada articulo.
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
