<div id="printEntregaRopaEpp-{{ $entrega->id }}" class="d-none">
    <div class="ppe-delivery-sheet">
        <h1>Constancia de entrega de ropa y EPP</h1>
        <p class="sheet-subtitle">Entrega #{{ $entrega->id }} - {{ $entrega->fecha_entrega?->format('d/m/Y') }}</p>

        <table>
            <thead>
                <tr class="delivery-repeat-heading">
                    <th colspan="5">
                        <div class="sheet-grid">
                            <div><strong>Entrega:</strong> #{{ $entrega->id }}</div>
                            <div><strong>Fecha:</strong> {{ $entrega->fecha_entrega?->format('d/m/Y') }}</div>
                            <div><strong>Empleado:</strong> {{ trim(($entrega->empleado?->apellidos ?? '') . ' ' . ($entrega->empleado?->nombres ?? '')) ?: 'N/A' }}</div>
                            <div><strong>Documento:</strong> {{ trim(($entrega->empleado?->tipo_doc ?? '') . ' ' . ($entrega->empleado?->numero_doc ?? '')) ?: '-' }}</div>
                            <div><strong>Deposito:</strong> {{ $entrega->deposito?->nombre ?? '-' }}</div>
                            <div><strong>Registrado por:</strong> {{ $entrega->usuario?->name ?? '-' }}</div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Item</th>
                    <th>Prenda / EPP</th>
                    <th>Codigo</th>
                    <th>Cant.</th>
                    <th>Condicion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entrega->detalles as $detalle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                        <td>{{ $detalle->articulo?->codigo_producto ?? '-' }}</td>
                        <td>{{ $detalle->cantidad_entregada }}</td>
                        <td>{{ $detalle->condicion_entrega ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Sin ropa/EPP cargados.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="delivery-repeat-footer">
                    <td colspan="5">
                        <p class="sheet-text">
                            Dejo constancia de haber recibido la ropa y los EPP detallados, comprometiendome a su cuidado,
                            uso laboral correspondiente y devolucion cuando sean requeridos por la empresa.
                        </p>

                        <div class="signature-grid">
                            <div class="signature-line">Firma empleado</div>
                            <div class="signature-line">Aclaracion y documento</div>
                            <div class="signature-line">Firma responsable</div>
                            <div class="signature-line">Fecha</div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        @if ($entrega->observaciones)
            <div class="sheet-observations">
                <strong>Observaciones generales:</strong>
                <span>{{ $entrega->observaciones }}</span>
            </div>
        @endif
    </div>
</div>
