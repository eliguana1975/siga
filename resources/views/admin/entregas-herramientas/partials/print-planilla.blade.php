<div id="printEntregaHerramienta-{{ $entrega->id }}" class="d-none">
    <div class="tool-delivery-sheet">
        <h1>Constancia de entrega de herramientas</h1>
        <p class="sheet-subtitle">Entrega #{{ $entrega->id }} - {{ $entrega->fecha_entrega?->format('d/m/Y') }}</p>

        <table>
            <thead>
                <tr class="delivery-repeat-heading">
                    <th colspan="4">
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
                    <th>Herramienta</th>
                    <th>Cant.</th>
                    <th>Condicion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entrega->detalles as $detalle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                        <td>{{ $detalle->cantidad_entregada }}</td>
                        <td>{{ $detalle->condicion_entrega ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Sin herramientas cargadas.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="delivery-repeat-footer">
                    <td colspan="4">
                        <p class="sheet-text">
                            Dejo constancia de haber recibido las herramientas detalladas, comprometiendome a su cuidado,
                            uso laboral correspondiente y devolucion cuando sean requeridas por la empresa.
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
