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

    $importeImpuestosProveedor = $compra->importeImpuestosProveedor();
    $totalConImpuestos = $compra->totalConImpuestos();
@endphp

@push('styles')
    <style>
        .receipt-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem 1.25rem;
        }

        .receipt-summary-label {
            display: block;
            color: var(--bs-secondary-color);
            font-size: .8rem;
            margin-bottom: .2rem;
        }

        .receipt-summary-value {
            font-weight: 700;
        }

        .receipt-section {
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            padding: 1rem;
        }

        .receipt-section-title {
            color: var(--bs-secondary-color);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .02em;
            margin-bottom: .75rem;
            text-transform: uppercase;
        }

        @media (max-width: 991.98px) {
            .receipt-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .receipt-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Comprobante de pago - Orden #{{ $compra->id }}</h3>
                <p class="text-subtitle text-muted">Carga la documentacion recibida para el pago registrado.</p>
            </div>
            <a href="{{ route('admin.ordenes-compra.pagos.create', $compra->id) }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver a pagos
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Informacion de la orden y pago</h4>
                </div>
                <div class="card-body">
                    <div class="receipt-summary-grid">
                        <div>
                            <span class="receipt-summary-label">Proveedor</span>
                            <div class="receipt-summary-value">{{ $compra->proveedorResumen() }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Deposito</span>
                            <div class="receipt-summary-value">{{ $compra->deposito?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Estado orden</span>
                            <span class="badge {{ $estadoBadges[$compra->estado] ?? 'bg-light-secondary' }}">
                                {{ $estadoLabels[$compra->estado] ?? ucfirst((string) $compra->estado) }}
                            </span>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Fecha orden</span>
                            <div class="receipt-summary-value">{{ $compra->fecha_compra?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Subtotal orden</span>
                            <div class="receipt-summary-value">${{ number_format((float) $compra->total_compra, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Impuestos orden</span>
                            <div class="receipt-summary-value">${{ number_format((float) $importeImpuestosProveedor, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Total orden</span>
                            <div class="receipt-summary-value">${{ number_format((float) $totalConImpuestos, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Usuario pago</span>
                            <div class="receipt-summary-value">{{ $pago->usuario?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Forma pago</span>
                            <div class="receipt-summary-value">{{ $pago->formaPagoLabel() }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Importe pago</span>
                            <div class="receipt-summary-value">${{ number_format((float) $pago->importe, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Fecha pago</span>
                            <div class="receipt-summary-value">{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="receipt-summary-label">Comprobante</span>
                            @if ($pago->tieneComprobante())
                                <span class="badge bg-light-success">Cargado</span>
                            @else
                                <span class="badge bg-light-warning">Pendiente</span>
                            @endif
                        </div>
                    </div>

                    @if (in_array($pago->forma_pago, ['cheque', 'e_check'], true))
                        <div class="receipt-section mt-4">
                            <div class="receipt-section-title">Cheque / ECheq registrado</div>
                            <div class="receipt-summary-grid">
                                <div>
                                    <span class="receipt-summary-label">Tipo</span>
                                    <div class="receipt-summary-value">{{ $pago->tipo_cheque === 'fisico' ? 'Cheque fisico' : ($pago->tipo_cheque === 'e_check' ? 'ECheq' : ($pago->tipo_cheque === 'terceros' ? 'Cheque de terceros' : '-')) }}</div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">Numero</span>
                                    <div class="receipt-summary-value">
                                        @forelse ($pago->numerosCheques() as $index => $numeroCheque)
                                            <span class="d-block">{{ $index + 1 }}. {{ $numeroCheque }}</span>
                                        @empty
                                            {{ $pago->nro_operacion_cheque ?: '-' }}
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">Banco</span>
                                    <div class="receipt-summary-value">{{ ($pago->bancoSeleccionado?->nombre ?? $pago->banco) ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">Titular</span>
                                    <div class="receipt-summary-value">{{ $pago->titular_cheque ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">CUIT librador</span>
                                    <div class="receipt-summary-value">{{ $pago->cuit_librador ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">Cuenta</span>
                                    <div class="receipt-summary-value">{{ $pago->nro_cuenta_cheque ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">Operacion / ID</span>
                                    <div class="receipt-summary-value">{{ $pago->nro_operacion_cheque ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="receipt-summary-label">Fecha pago / emision</span>
                                    <div class="receipt-summary-value">{{ $pago->fecha_emision_cheque?->format('d/m/Y') ?? '-' }}</div>
                                </div>
                            </div>

                            @if (count($pago->vencimientosPago()) > 0)
                                <div class="mt-3">
                                    <span class="receipt-summary-label">Vencimientos calculados</span>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($pago->vencimientosPago() as $vencimiento)
                                            <span class="badge bg-light-primary">{{ \Carbon\Carbon::parse($vencimiento)->format('d/m/Y') }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Comprobante recibido</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ordenes-compra.pagos.comprobante.update', [$compra->id, $pago->id]) }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12 col-md-4">
                            <label class="form-label">Fecha comprobante</label>
                            <input type="date" name="fecha_comprobante_pago" class="form-control" value="{{ old('fecha_comprobante_pago', $pago->fecha_comprobante_pago?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nro. recibo</label>
                            <input type="text" name="nro_recibo" class="form-control" value="{{ old('nro_recibo', $pago->nro_recibo) }}" maxlength="120">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nro. comprobante</label>
                            <input type="text" name="nro_comprobante_pago" class="form-control" value="{{ old('nro_comprobante_pago', $pago->nro_comprobante_pago) }}" maxlength="120">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nro. transferencia / operacion</label>
                            <input type="text" name="nro_transferencia" class="form-control" value="{{ old('nro_transferencia', $pago->nro_transferencia) }}" maxlength="120">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones del comprobante</label>
                            <textarea name="observaciones_comprobante" class="form-control" rows="3">{{ old('observaciones_comprobante', $pago->observaciones_comprobante) }}</textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.ordenes-compra.pagos.create', $compra->id) }}" class="btn btn-light-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar comprobante
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
