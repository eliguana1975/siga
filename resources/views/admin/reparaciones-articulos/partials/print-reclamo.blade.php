<div id="printReparacionReclamo-{{ $reparacion->id }}" class="d-none">
    <div class="repair-sheet">
        <h1>Planilla de reclamo de reparacion</h1>
        <p class="sheet-subtitle">Orden {{ $reparacion->numero_orden }} - Pendientes a reclamar</p>

        <table>
            <thead>
                <tr class="repair-repeat-heading">
                    <th colspan="6">
                        <div class="sheet-grid">
                            <div><strong>Proveedor:</strong> {{ $reparacion->proveedor?->nombre ?? '-' }}</div>
                            <div><strong>Telefono:</strong> {{ $reparacion->telefono ?: '-' }}</div>
                            <div><strong>Fecha envio:</strong> {{ $reparacion->fecha_envio?->format('d/m/Y') }}</div>
                            <div><strong>Fecha compromiso:</strong> {{ $reparacion->fecha_compromiso?->format('d/m/Y') ?? '-' }}</div>
                            <div><strong>Domicilio:</strong> {{ $reparacion->domicilio ?: '-' }}</div>
                            <div><strong>Codigo postal:</strong> {{ $reparacion->codigo_postal ?: '-' }}</div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Item</th>
                    <th>Articulo</th>
                    <th>Cant. enviada</th>
                    <th>Cant. devuelta</th>
                    <th>Pendiente</th>
                    <th>Dias transcurridos</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pendientes = $reparacion->detalles->filter(fn ($detalle) => $detalle->cantidadPendiente() > 0);
                @endphp
                @forelse ($pendientes as $detalle)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detalle->nombreArticulo() }}<br><small>{{ $detalle->codigoArticulo() }}</small></td>
                        <td>{{ $detalle->cantidad_enviada }}</td>
                        <td>{{ $detalle->cantidad_devuelta }}</td>
                        <td>{{ $detalle->cantidadPendiente() }}</td>
                        <td>{{ $reparacion->fecha_envio ? $reparacion->fecha_envio->diffInDays(now()) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay pendientes para reclamar.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="repair-repeat-footer">
                    <td colspan="6">
                        <p class="sheet-text">
                            Se solicita la devolucion de los articulos pendientes detallados. Esta planilla deja constancia del reclamo.
                        </p>
                        <div class="signature-grid">
                            <div class="signature-line">Firma solicitante</div>
                            <div class="signature-line">Aclaracion</div>
                            <div class="signature-line">Firma proveedor</div>
                            <div class="signature-line">Fecha compromiso nueva</div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
