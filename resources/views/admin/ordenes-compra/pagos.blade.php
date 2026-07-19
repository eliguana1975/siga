@extends('layouts.admin')

@php
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

    $impuestosPago = $impuestosPago ?? [];
    $importeImpuestosProveedor = $compra->importeImpuestosProveedor();
    $totalConImpuestos = $compra->totalConImpuestos();
    $totalPagado = $compra->totalPagado();
    $saldoConImpuestos = $compra->saldoPendienteConImpuestos();
    $porcentajeImpuestosPago = collect($impuestosPago)->sum(fn ($impuesto) => (float) ($impuesto['porcentaje'] ?? 0));
    $importeTotalSugeridoPago = $saldoConImpuestos;
    $puedeRegistrarPago = round((float) $saldoConImpuestos, 2) > 0;
    $proveedorPago = $proveedorPago ?? $compra->proveedor;
    $bancos = $bancos ?? collect();
    $ajusteEmpresa = $ajusteEmpresa ?? null;
@endphp

@push('styles')
    <style>
        .payment-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem 1.25rem;
        }

        .payment-summary-label {
            display: block;
            color: var(--bs-secondary-color);
            font-size: .8rem;
            margin-bottom: .2rem;
        }

        .payment-summary-value {
            font-weight: 700;
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

        .payment-tax-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 110px 36px 160px;
            gap: .5rem;
            align-items: center;
        }

        .payment-tax-name {
            min-width: 0;
            white-space: nowrap;
        }

        .payment-tax-rate,
        .payment-tax-symbol,
        .payment-tax-amount {
            min-height: 34px;
            display: flex;
            align-items: center;
        }

        .payment-tax-rate {
            justify-content: flex-end;
        }

        .payment-tax-symbol {
            justify-content: center;
        }

        .payment-tax-amount {
            justify-content: flex-end;
            font-weight: 700;
            white-space: nowrap;
        }

        .payment-due-preview {
            background-color: var(--bs-body-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
            min-height: 2.4rem;
        }

        .payment-check-number-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        @media (max-width: 991.98px) {
            .payment-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .payment-summary-grid,
            .payment-tax-row,
            .payment-check-number-grid {
                grid-template-columns: 1fr;
            }

            .payment-tax-name {
                white-space: normal;
            }

            .payment-tax-rate,
            .payment-tax-symbol,
            .payment-tax-amount {
                justify-content: flex-start;
                min-height: auto;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Registrar pago - Orden de compra #{{ $compra->id }}</h3>
                <p class="text-subtitle text-muted">Consulta la orden y registra un pago sin modificar sus articulos.</p>
            </div>
            <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Informacion de la orden</h4>
                </div>
                <div class="card-body">
                    <div class="payment-summary-grid">
                        <div>
                            <span class="payment-summary-label">Deposito</span>
                            <div class="payment-summary-value">{{ $compra->deposito?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Proveedor</span>
                            <div class="payment-summary-value">{{ $compra->proveedorResumen() }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Pedido</span>
                            <div class="payment-summary-value">
                                @if ($compra->pedidoArticulo)
                                    Pedido #{{ $compra->pedidoArticulo->id }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Fecha</span>
                            <div class="payment-summary-value">{{ $compra->fecha_compra?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Forma de pago sugerida</span>
                            <div class="payment-summary-value">{{ $compra->formaPagoLabel() }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Condicion / datos de pago</span>
                            <div class="payment-summary-value">
                                {{ $proveedorPago?->condicionPagoLabel() ?? 'Sin definir' }}
                                @if ($compra->datos_pago || $proveedorPago?->datos_pago)
                                    <small class="d-block text-muted">{{ $compra->datos_pago ?: $proveedorPago?->datos_pago }}</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Estado orden</span>
                            <span class="badge {{ $estadoBadges[$compra->estado] ?? 'bg-light-secondary' }}">
                                {{ $estadoLabels[$compra->estado] ?? ucfirst((string) $compra->estado) }}
                            </span>
                        </div>
                        <div>
                            <span class="payment-summary-label">Usuario</span>
                            <div class="payment-summary-value">{{ $compra->usuario?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Subtotal</span>
                            <div class="payment-summary-value">${{ number_format((float) $compra->total_compra, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Impuestos</span>
                            <div class="payment-summary-value">${{ number_format((float) $importeImpuestosProveedor, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Total con impuestos</span>
                            <div class="payment-summary-value">${{ number_format((float) $totalConImpuestos, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="payment-summary-label">Saldo pendiente</span>
                            <div class="payment-summary-value">${{ number_format((float) $saldoConImpuestos, 2, ',', '.') }}</div>
                        </div>
                    </div>

                    @if ($compra->notas)
                        <div class="mt-3">
                            <span class="payment-summary-label">Notas</span>
                            <div>{{ $compra->notas }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Registrar pago</h4>
                </div>
                <div class="card-body">
                    @unless ($puedeRegistrarPago)
                        <div class="alert alert-success">
                            Esta orden ya tiene registrado el pago total. No quedan saldos pendientes para pagar.
                        </div>
                    @endunless

                    <form id="compraPagoForm" method="POST" action="{{ route('admin.ordenes-compra.pagos.store', $compra->id) }}" class="row g-3 mb-4">
                        @csrf
                        <div class="col-12 col-md-3">
                            <label class="form-label">Forma de pago (*)</label>
                            <select id="pagoFormaPago" name="forma_pago" class="form-select" required>
                                <option value="">Seleccione forma de pago</option>
                                @foreach (\App\Models\Compra::formasPago() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('forma_pago', $compra->forma_pago) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Tipo de pago (*)</label>
                            <select id="pagoTipoPago" name="tipo_pago" class="form-select" required>
                                <option value="total" @selected(old('tipo_pago', 'total') === 'total')>Pago total</option>
                                <option value="parcial" @selected(old('tipo_pago') === 'parcial')>Pago parcial</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3" data-partial-payment-section>
                            <label class="form-label">Porcentaje parcial</label>
                            <div class="input-group">
                                <input id="pagoPorcentaje" type="text" name="porcentaje_pago" class="form-control" value="{{ old('porcentaje_pago') }}" inputmode="decimal" placeholder="50">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Importe total (*)</label>
                            <input id="pagoImporteBase" type="text" name="importe" class="form-control" value="{{ old('importe', number_format($importeTotalSugeridoPago, 2, ',', '.')) }}" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Fecha de pago</label>
                            <input id="pagoFechaPago" type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12">
                            <div class="payment-section">
                                <div class="payment-section-title">Impuestos aplicables</div>
                                @forelse ($impuestosPago as $index => $impuesto)
                                    <div class="payment-tax-row mb-2" data-tax-row>
                                        <div class="payment-tax-name">
                                            <input type="hidden" name="impuestos_pago[{{ $index }}][nombre]" value="{{ $impuesto['nombre'] }}">
                                            <input type="hidden" name="impuestos_pago[{{ $index }}][origen]" value="{{ $impuesto['origen'] }}">
                                            <input type="hidden" name="impuestos_pago[{{ $index }}][aplicar]" value="1" class="js-payment-tax-check" data-tax-checked="1">
                                            <span class="fw-semibold">
                                                {{ $impuesto['nombre'] }}
                                                <small class="text-muted">({{ $impuesto['origen'] }})</small>
                                            </span>
                                            @if (! empty($impuesto['descripcion']))
                                                <small class="text-muted ms-2">{{ $impuesto['descripcion'] }}</small>
                                            @endif
                                        </div>
                                        <div class="payment-tax-rate">
                                            <input type="hidden" name="impuestos_pago[{{ $index }}][porcentaje]" class="js-payment-tax-rate" value="{{ number_format((float) $impuesto['porcentaje'], 4, '.', '') }}">
                                            <span>{{ number_format((float) $impuesto['porcentaje'], 4, ',', '.') }}</span>
                                        </div>
                                        <div class="payment-tax-symbol border rounded">
                                            %
                                        </div>
                                        <div class="payment-tax-amount">
                                            <span class="js-payment-tax-amount">$0,00</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No hay impuestos activos configurados.</p>
                                @endforelse

                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Base</span>
                                        <strong id="pagoBasePreview">$0,00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Impuestos</span>
                                        <strong id="pagoImpuestosPreview">$0,00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between fs-5 mt-1">
                                        <span>Total a pagar</span>
                                        <strong id="pagoTotalPreview">$0,00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" data-payment-section="cheque">
                            <div class="payment-section">
                                <div class="payment-section-title">Cheque / ECheq</div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Tipo</label>
                                        <select id="pagoTipoCheque" name="tipo_cheque" class="form-select">
                                            <option value="">Seleccione tipo</option>
                                            <option value="fisico" @selected(old('tipo_cheque') === 'fisico')>Cheque fisico</option>
                                            <option value="e_check" @selected(old('tipo_cheque') === 'e_check')>ECheq</option>
                                            <option value="terceros" @selected(old('tipo_cheque') === 'terceros')>Cheque de terceros</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Banco</label>
                                        <select name="banco_id" class="form-select">
                                            <option value="">Seleccione banco</option>
                                            @foreach ($bancos as $banco)
                                                <option value="{{ $banco->id }}" @selected((string) old('banco_id') === (string) $banco->id)>{{ $banco->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Titular / librador</label>
                                        <input type="text" name="titular_cheque" class="form-control" value="{{ old('titular_cheque', $ajusteEmpresa?->nombre) }}" maxlength="180">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">CUIT librador</label>
                                        <input type="text" name="cuit_librador" class="form-control" value="{{ old('cuit_librador', $ajusteEmpresa?->cuit) }}" maxlength="30">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Cuenta</label>
                                        <input type="text" name="nro_cuenta_cheque" class="form-control" value="{{ old('nro_cuenta_cheque') }}" maxlength="80">
                                    </div>
                                    <div class="col-12 col-md-3" data-echeq-operation>
                                        <label class="form-label">Operacion / ID ECheq</label>
                                        <input type="text" name="nro_operacion_cheque" class="form-control" value="{{ old('nro_operacion_cheque') }}" maxlength="120">
                                    </div>
                                    <input id="pagoPlazo" type="hidden" name="plazo_pago" value="{{ old('plazo_pago') }}">
                                    <div class="col-12" data-check-number-section>
                                        <label class="form-label">Nros. de cheques</label>
                                        <div id="pagoNumerosCheques" class="payment-check-number-grid"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Vencimientos calculados</label>
                                        <div id="pagoVencimientosPreview" class="payment-due-preview form-control"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" @disabled(! $puedeRegistrarPago) title="{{ $puedeRegistrarPago ? 'Registrar pago' : 'Pago total ya registrado' }}">
                                <i class="bi bi-cash-coin"></i> Registrar pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>

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
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compra->pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $pago->formaPagoLabel() }}</td>
                                        <td>
                                            ${{ number_format((float) $pago->importe, 2, ',', '.') }}
                                            @if ($pago->tipo_pago)
                                                <small class="d-block text-muted">
                                                    {{ $pago->tipo_pago === 'parcial' ? 'Pago parcial' : 'Pago total' }}
                                                    @if ($pago->porcentaje_pago)
                                                        {{ number_format((float) $pago->porcentaje_pago, 2, ',', '.') }}%
                                                    @endif
                                                </small>
                                            @endif
                                            @if ($pago->importe_base !== null)
                                                <small class="d-block text-muted">Base ${{ number_format((float) $pago->importe_base, 2, ',', '.') }}</small>
                                            @endif
                                            @if ((float) $pago->importe_impuestos > 0)
                                                <small class="d-block text-muted">Imp. ${{ number_format((float) $pago->importe_impuestos, 2, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @forelse ($pago->numerosCheques() as $index => $numeroCheque)
                                                <span class="d-block">{{ $index + 1 }}. {{ $numeroCheque }}</span>
                                            @empty
                                                {{ $pago->nro_operacion_cheque ?: '-' }}
                                            @endforelse
                                            @if ($pago->tipo_cheque)
                                                <small class="d-block text-muted">{{ $pago->tipo_cheque === 'fisico' ? 'Cheque fisico' : ($pago->tipo_cheque === 'e_check' ? 'ECheq' : 'Cheque de terceros') }}</small>
                                            @endif
                                            @if ($pago->bancoSeleccionado?->nombre || $pago->banco)
                                                <small class="d-block text-muted">{{ $pago->bancoSeleccionado?->nombre ?? $pago->banco }}</small>
                                            @endif
                                            @if ($pago->titular_cheque)
                                                <small class="d-block text-muted">Titular {{ $pago->titular_cheque }}</small>
                                            @endif
                                            @if ($pago->plazo_pago !== null && $pago->plazo_pago !== '')
                                                <small class="d-block text-muted">
                                                    Plazo {{ $pago->plazo_pago === '0' ? 'a la vista' : str_replace('-', ' - ', $pago->plazo_pago) . ' dias' }}
                                                </small>
                                            @endif
                                            @foreach ($pago->vencimientosPago() as $vencimiento)
                                                <small class="d-block text-muted">Vence {{ \Carbon\Carbon::parse($vencimiento)->format('d/m/Y') }}</small>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if ($pago->tieneComprobante())
                                                <span class="badge bg-light-success">Cargado</span>
                                            @else
                                                <span class="badge bg-light-warning">Pendiente</span>
                                            @endif
                                            @if ($pago->nro_comprobante_pago)
                                                <small class="d-block text-muted">Comp. {{ $pago->nro_comprobante_pago }}</small>
                                            @endif
                                            @if ($pago->nro_transferencia)
                                                <small class="d-block text-muted">Transf. {{ $pago->nro_transferencia }}</small>
                                            @endif
                                            @if ($pago->nro_recibo)
                                                <small class="d-block text-muted">Recibo {{ $pago->nro_recibo }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $pago->usuario?->name ?? '-' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.ordenes-compra.pagos.comprobante', [$compra->id, $pago->id]) }}" class="btn btn-sm btn-primary" title="Cargar comprobante">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.ordenes-compra.pagos.destroy', [$compra->id, $pago->id]) }}" class="d-inline" onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar este pago?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar pago">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No hay pagos registrados para esta orden.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total pagado</th>
                                    <th>${{ number_format($totalPagado, 2, ',', '.') }}</th>
                                    <th colspan="4" class="text-muted">Saldo pendiente: ${{ number_format($saldoConImpuestos, 2, ',', '.') }}</th>
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
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compra->detalles as $detalle)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $detalle->articulo?->nombre ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $detalle->articulo?->codigo_producto ?: '-' }}</small>
                                        </td>
                                        <td>{{ $detalle->proveedor?->nombre ?? $compra->proveedor?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>${{ number_format((float) $detalle->precio_compra_unidad, 2, ',', '.') }}</td>
                                        <td>${{ number_format((float) $detalle->precio_compra_unidad * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No hay articulos cargados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total con impuestos</th>
                                    <th>${{ number_format($totalConImpuestos, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const proveedor = @json($proveedorPago);
            const oldNumerosCheques = @json(array_values((array) old('nros_cheques', [])));
            const saldoPendientePago = Number(@json(round((float) $saldoConImpuestos, 2)));
            const pagoFormaPago = document.getElementById('pagoFormaPago');
            const pagoTipoPago = document.getElementById('pagoTipoPago');
            const pagoPorcentaje = document.getElementById('pagoPorcentaje');
            const pagoImporteBase = document.getElementById('pagoImporteBase');
            const pagoFechaPago = document.getElementById('pagoFechaPago');
            const pagoTipoCheque = document.getElementById('pagoTipoCheque');
            const pagoPlazo = document.getElementById('pagoPlazo');
            const pagoVencimientosPreview = document.getElementById('pagoVencimientosPreview');
            const pagoNumerosCheques = document.getElementById('pagoNumerosCheques');
            const checkNumberSection = document.querySelector('[data-check-number-section]');
            const echeqOperation = document.querySelector('[data-echeq-operation]');
            const pagoBasePreview = document.getElementById('pagoBasePreview');
            const pagoImpuestosPreview = document.getElementById('pagoImpuestosPreview');
            const pagoTotalPreview = document.getElementById('pagoTotalPreview');
            const partialPaymentSection = document.querySelector('[data-partial-payment-section]');
            const paymentSections = document.querySelectorAll('[data-payment-section]');
            const paymentTaxRows = document.querySelectorAll('[data-tax-row]');

            function money(value) {
                return new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS'
                }).format(Number(value || 0));
            }

            function parseMoney(value) {
                const raw = String(value ?? '').trim();

                if (raw === '') {
                    return 0;
                }

                if (raw.includes(',')) {
                    return Number(raw.replace(/\./g, '').replace(',', '.')) || 0;
                }

                if (/^\d{1,3}(\.\d{3})+$/.test(raw)) {
                    return Number(raw.replace(/\./g, '')) || 0;
                }

                return Number(raw) || 0;
            }

            function inferProveedorPaymentTerm() {
                if (proveedor?.condicion_pago_dias) {
                    return String(proveedor.condicion_pago_dias).replace(/\s+/g, '');
                }

                const text = String(proveedor?.datos_pago || '').toLowerCase();
                const patterns = [
                    /(\d+\s*(?:-\s*\d+\s*){1,4})\s*(?:dias|días)?/,
                    /(?:a|de)?\s*(\d+)\s*(?:dias|días)/,
                ];

                for (const pattern of patterns) {
                    const match = text.match(pattern);

                    if (match) {
                        return match[1].replace(/\s+/g, '');
                    }
                }

                return '';
            }

            function applyPaymentDefaultsFromProveedor() {
                if (!proveedor) {
                    return;
                }

                if (pagoFormaPago && !pagoFormaPago.value && proveedor.forma_pago_preferida) {
                    pagoFormaPago.value = proveedor.forma_pago_preferida;

                    if (window.jQuery) {
                        window.jQuery(pagoFormaPago).trigger('change.select2');
                    }
                }

                const inferredTerm = inferProveedorPaymentTerm();

                if (pagoPlazo && inferredTerm) {
                    pagoPlazo.value = inferredTerm;
                }
            }

            function parseLocalDate(value) {
                const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

                if (!match) {
                    return null;
                }

                return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
            }

            function toInputDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            }

            function toDisplayDate(date) {
                return new Intl.DateTimeFormat('es-AR').format(date);
            }

            function getPaymentDueDates() {
                const issueDate = parseLocalDate(pagoFechaPago?.value);
                const term = String(pagoPlazo?.value || '').trim();

                if (!issueDate || term === '') {
                    return [];
                }

                return term.split('-')
                    .map(days => Number(String(days).trim()))
                    .filter(days => Number.isFinite(days))
                    .map(days => {
                        const dueDate = new Date(issueDate.getTime());
                        dueDate.setDate(dueDate.getDate() + days);
                        return dueDate;
                    });
            }

            function renderChequeNumberFields(dueDates) {
                const selectedType = String(pagoTipoCheque?.value || '');
                const isPhysicalCheck = ['fisico', 'terceros'].includes(selectedType);
                const isEcheq = selectedType === 'e_check';

                checkNumberSection?.classList.toggle('d-none', !isPhysicalCheck);
                echeqOperation?.classList.toggle('d-none', !isEcheq);

                if (!pagoNumerosCheques) {
                    return;
                }

                if (!isPhysicalCheck || dueDates.length === 0) {
                    pagoNumerosCheques.innerHTML = '';
                    return;
                }

                const currentValues = Array.from(pagoNumerosCheques.querySelectorAll('input'))
                    .map(input => input.value);

                pagoNumerosCheques.innerHTML = dueDates.map((date, index) => {
                    const value = currentValues[index] ?? oldNumerosCheques[index] ?? '';
                    const label = dueDates.length === 1
                        ? 'Nro. cheque'
                        : `Nro. cheque ${index + 1}`;

                    return `
                        <div>
                            <label class="form-label">${label}</label>
                            <input type="text" name="nros_cheques[]" class="form-control" value="${String(value).replace(/"/g, '&quot;')}" maxlength="120">
                        </div>
                    `;
                }).join('');
            }

            function updatePaymentDueDates() {
                const dueDates = getPaymentDueDates();

                if (!pagoVencimientosPreview) {
                    renderChequeNumberFields(dueDates);
                    return;
                }

                if (dueDates.length === 0) {
                    pagoVencimientosPreview.textContent = 'No se detecto plazo en la condicion de pago del proveedor.';
                    renderChequeNumberFields(dueDates);
                    return;
                }

                pagoVencimientosPreview.textContent = dueDates
                    .map((date, index) => `${index + 1}. ${toDisplayDate(date)}`)
                    .join(' | ');

                renderChequeNumberFields(dueDates);
            }

            function updatePaymentSections() {
                const selected = String(pagoFormaPago?.value || '');

                paymentSections.forEach(section => {
                    const type = section.dataset.paymentSection;
                    const visible = type === 'cheque' && ['cheque', 'e_check'].includes(selected);

                    section.classList.toggle('d-none', !visible);
                });

                updatePaymentDueDates();
            }

            function updatePaymentTaxes() {
                const total = parseMoney(pagoImporteBase?.value || 0);
                let totalRate = 0;

                paymentTaxRows.forEach(row => {
                    const checkbox = row.querySelector('.js-payment-tax-check');
                    const rateInput = row.querySelector('.js-payment-tax-rate');
                    const isApplied = checkbox?.checked || checkbox?.dataset.taxChecked === '1' || checkbox?.value === '1';

                    if (isApplied) {
                        totalRate += parseMoney(rateInput?.value || 0);
                    }
                });

                const base = totalRate > 0 ? total / (1 + (totalRate / 100)) : total;
                let taxTotal = 0;

                paymentTaxRows.forEach(row => {
                    const checkbox = row.querySelector('.js-payment-tax-check');
                    const rateInput = row.querySelector('.js-payment-tax-rate');
                    const amountLabel = row.querySelector('.js-payment-tax-amount');
                    const isApplied = checkbox?.checked || checkbox?.dataset.taxChecked === '1' || checkbox?.value === '1';
                    const amount = isApplied ? base * parseMoney(rateInput?.value || 0) / 100 : 0;

                    taxTotal += amount;

                    if (amountLabel) {
                        amountLabel.textContent = money(amount);
                    }
                });

                if (pagoBasePreview) {
                    pagoBasePreview.textContent = money(base);
                }

                if (pagoImpuestosPreview) {
                    pagoImpuestosPreview.textContent = money(taxTotal);
                }

                if (pagoTotalPreview) {
                    pagoTotalPreview.textContent = money(total);
                }
            }

            function formatDecimal(value) {
                return new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(Number(value || 0));
            }

            function updatePaymentType() {
                const isPartial = pagoTipoPago?.value === 'parcial';

                partialPaymentSection?.classList.toggle('d-none', !isPartial);

                if (!isPartial) {
                    if (pagoImporteBase) {
                        pagoImporteBase.value = formatDecimal(saldoPendientePago);
                    }
                    if (pagoPorcentaje) {
                        pagoPorcentaje.value = '';
                    }
                    updatePaymentTaxes();
                    return;
                }

                const percentage = parseMoney(pagoPorcentaje?.value || 0);

                if (percentage > 0 && pagoImporteBase) {
                    pagoImporteBase.value = formatDecimal(saldoPendientePago * percentage / 100);
                }

                updatePaymentTaxes();
            }

            pagoFormaPago?.addEventListener('change', updatePaymentSections);
            pagoTipoPago?.addEventListener('change', updatePaymentType);
            pagoPorcentaje?.addEventListener('input', updatePaymentType);
            pagoImporteBase?.addEventListener('input', updatePaymentTaxes);
            pagoFechaPago?.addEventListener('change', updatePaymentDueDates);
            pagoTipoCheque?.addEventListener('change', updatePaymentDueDates);

            paymentTaxRows.forEach(row => {
                row.querySelector('.js-payment-tax-check')?.addEventListener('change', updatePaymentTaxes);
            });

            if (window.jQuery) {
                window.jQuery(pagoFormaPago).on('select2:select change', updatePaymentSections);
                window.jQuery(pagoTipoPago).on('select2:select change', updatePaymentType);
                window.jQuery(pagoTipoCheque).on('select2:select change', updatePaymentDueDates);
            }

            applyPaymentDefaultsFromProveedor();
            updatePaymentType();
            updatePaymentSections();
            updatePaymentTaxes();
        });
    </script>
@endpush
