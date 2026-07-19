<div id="printTransferenciaDeposito-{{ $transferencia->id }}" class="d-none">
    @php
        $numeroTransferencia = 'TR-' . str_pad((string) $transferencia->id, 6, '0', STR_PAD_LEFT);
    @endphp

    <div class="transfer-sheet">
        <h1>Transferencia entre depositos</h1>
        <p class="sheet-subtitle">{{ $numeroTransferencia }} - {{ $transferencia->fecha_transferencia?->format('d/m/Y') }}</p>

        <table>
            <thead>
                <tr class="transfer-repeat-heading">
                    <th colspan="4">
                        <div class="sheet-grid">
                            <div><strong>Nro transferencia:</strong> {{ $numeroTransferencia }}</div>
                            <div><strong>Fecha:</strong> {{ $transferencia->fecha_transferencia?->format('d/m/Y') }}</div>
                            <div><strong>Origen:</strong> {{ $transferencia->depositoOrigen?->nombre ?? 'N/A' }}</div>
                            <div><strong>Destino:</strong> {{ $transferencia->depositoDestino?->nombre ?? 'N/A' }}</div>
                            <div><strong>Usuario:</strong> {{ $transferencia->usuario?->name ?? 'N/A' }}</div>
                            <div><strong>Estado:</strong> {{ ucfirst((string) ($transferencia->estado ?: 'confirmada')) }}</div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Item</th>
                    <th>Articulo</th>
                    <th>Cant.</th>
                    <th>Unidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transferencia->detalles as $detalle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Sin articulos cargados.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="transfer-repeat-footer">
                    <td colspan="4">
                        <div class="sheet-summary">
                            <strong>Total transferido:</strong> {{ (int) $transferencia->detalles->sum('cantidad') }} unidad(es) en {{ $transferencia->detalles->count() }} articulo(s)
                        </div>

                        <div class="signature-grid">
                            <div class="signature-line">Entrega</div>
                            <div class="signature-line">Recibe</div>
                            <div class="signature-line">Control deposito origen</div>
                            <div class="signature-line">Control deposito destino</div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        @if ($transferencia->observaciones)
            <div class="sheet-observations">
                <strong>Observaciones:</strong>
                <span>{{ $transferencia->observaciones }}</span>
            </div>
        @endif
    </div>
</div>
