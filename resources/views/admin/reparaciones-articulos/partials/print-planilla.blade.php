<div id="printReparacionPlanilla-{{ $reparacion->id }}" class="d-none">
    <div class="repair-sheet">
        <h1>Planilla de envio a reparacion</h1>
        <p class="sheet-subtitle">Orden {{ $reparacion->numero_orden }} - {{ $reparacion->fecha_envio?->format('d/m/Y') }}</p>

        <table>
            <thead>
                <tr class="repair-repeat-heading">
                    <th colspan="5">
                        <div class="sheet-grid">
                            <div><strong>Orden:</strong> {{ $reparacion->numero_orden }}</div>
                            <div><strong>Fecha envio:</strong> {{ $reparacion->fecha_envio?->format('d/m/Y') }}</div>
                            <div><strong>Proveedor:</strong> {{ $reparacion->proveedor?->nombre ?? '-' }}</div>
                            <div><strong>Compromiso:</strong> {{ $reparacion->fecha_compromiso?->format('d/m/Y') ?? '-' }}</div>
                            <div><strong>Domicilio:</strong> {{ $reparacion->domicilio ?: '-' }}</div>
                            <div><strong>Telefono:</strong> {{ $reparacion->telefono ?: '-' }}</div>
                            <div><strong>Envia:</strong> {{ $reparacion->quien_envia_nombre ?: ($empresaNombreEnvio ?? '-') }}</div>
                            <div><strong>Doc. envia:</strong> {{ $reparacion->quien_envia_documento ?: '-' }}</div>
                            <div><strong>Recibe:</strong> {{ $reparacion->quien_recibe_nombre ?: '-' }}</div>
                            <div><strong>Doc. recibe:</strong> {{ $reparacion->quien_recibe_documento ?: '-' }}</div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Item</th>
                    <th>Articulo</th>
                    <th>Cant. enviada</th>
                    <th>Costo unit.</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reparacion->detalles as $detalle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detalle->nombreArticulo() }}<br><small>{{ $detalle->codigoArticulo() }}</small></td>
                        <td>{{ $detalle->cantidad_enviada }}</td>
                        <td>{{ $detalle->costo_unitario !== null ? number_format((float) $detalle->costo_unitario, 2, ',', '.') : '-' }}</td>
                        <td>{{ $detalle->observaciones ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Sin articulos cargados.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="repair-repeat-footer">
                    <td colspan="5">
                        <p class="sheet-text">
                            Se deja constancia del envio de los articulos detallados para su reparacion en el proveedor indicado.
                        </p>
                        <div class="signature-grid">
                            <div class="signature-line">Firma quien entrega</div>
                            <div class="signature-line">Aclaracion y documento</div>
                            <div class="signature-line">Firma proveedor</div>
                            <div class="signature-line">Fecha recepcion</div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        @if ($reparacion->observaciones)
            <div class="sheet-observations">
                <strong>Observaciones:</strong>
                <span>{{ $reparacion->observaciones }}</span>
            </div>
        @endif
    </div>
</div>
