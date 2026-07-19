<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de compra #{{ $compra->id }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 0; padding: 24px; background: #f8fafc;">
    <div style="max-width: 900px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 24px;">
        <div style="border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 16px;">
            <h1 style="margin: 0 0 4px; font-size: 24px;">Orden de compra #{{ $compra->id }}</h1>
            <p style="margin: 0; color: #6b7280;">Detalle general de la orden de compra.</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Deposito</strong><br>{{ $compra->deposito?->nombre ?? 'N/A' }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Proveedor</strong><br>{{ $compra->proveedorResumen() }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Fecha</strong><br>{{ $compra->fecha_compra?->format('d/m/Y') }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Total</strong><br>${{ number_format((float) $compra->total_compra, 2, ',', '.') }}<br><span style="color: #6b7280; font-size: 11px;">Importe sin impuestos.</span></td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Pedido</strong><br>{{ $compra->pedidoArticulo ? 'Pedido #' . $compra->pedidoArticulo->id : '-' }}</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Usuario</strong><br>{{ $compra->usuario?->name ?? 'N/A' }}</td>
                <td colspan="2" style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Notas</strong><br>{{ $compra->notas ?: '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Forma de pago</strong><br>{{ $compra->formaPagoLabel() }}</td>
                <td colspan="3" style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Datos de pago</strong><br>{{ $compra->datos_pago ?: '-' }}</td>
            </tr>
        </table>

        <h2 style="font-size: 18px; margin: 0 0 10px;">Detalle de articulos</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr>
                    <th style="text-align: left; background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px;">Articulo</th>
                    <th style="text-align: left; background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px;">Proveedor</th>
                    <th style="text-align: left; background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px;">Cantidad</th>
                    <th style="text-align: left; background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px;">Precio</th>
                    <th style="text-align: left; background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compra->detalles as $detalle)
                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 8px;">{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 8px;">{{ $detalle->proveedor?->nombre ?? $compra->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 8px;">{{ $detalle->cantidad }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 8px;">${{ number_format((float) $detalle->precio_compra_unidad, 2, ',', '.') }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 8px;">${{ number_format((float) $detalle->precio_compra_unidad * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="border: 1px solid #d1d5db; padding: 8px;">No hay detalles cargados para esta orden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <p style="margin: 8px 0 0; color: #6b7280; font-size: 12px; text-align: right;">
            Importes expresados sin impuestos.
        </p>
    </div>
</body>
</html>
