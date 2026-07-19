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

    $canViewOrdenesCompra = auth()->user()?->can('ordenes-compra.ver');
    $canCreateOrdenesCompra = auth()->user()?->can('ordenes-compra.crear');
    $canEditOrdenesCompra = auth()->user()?->can('ordenes-compra.editar');
    $canDeleteOrdenesCompra = auth()->user()?->can('ordenes-compra.eliminar');
    $canSendOrdenesCompraMail = auth()->user()?->can('ordenes-compra.enviar-mail');
    $showOrdenCompraActions = $canViewOrdenesCompra || $canEditOrdenesCompra || $canDeleteOrdenesCompra || $canSendOrdenesCompraMail;
@endphp

@push('styles')
    <style>
        .modal .input-group .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
        }

        .modal .input-group .input-group-text i {
            line-height: 1;
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Ordenes de compra</h3>
                <p class="text-subtitle text-muted">
                    Consulta las compras registradas por deposito, proveedor, usuario y estado.
                </p>
            </div>
            @if ($canCreateOrdenesCompra)
                <a href="{{ route('admin.ordenes-compra.create', ['clear' => 1]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva orden de compra
                </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de ordenes de compra registradas</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.ordenes-compra.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar orden de compra</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Numero de orden, proveedor, deposito, usuario, estado o notas">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'ordenes_compra_registradas')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'ordenes_compra_registradas')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'ordenes_compra_registradas')">
                                <i class="bi bi-file-earmark-pdf"></i> Crear PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="printTable('datatable')">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $compras->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Orden</th>
                                    <th>Deposito</th>
                                    <th>Pedido</th>
                                    <th>Proveedor</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Forma de pago</th>
                                    <th>Total</th>
                                    <th>Pagado</th>
                                    <th>Estado pago</th>
                                    <th>Estado</th>
                                    <th>Notas</th>
                                    @if ($showOrdenCompraActions)
                                        <th class="text-end" style="width: 190px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compras as $compra)
                                    @php
                                        $importeImpuestosProveedor = $compra->importeImpuestosProveedor();
                                        $totalConImpuestos = $compra->totalConImpuestos();
                                        $totalPagado = $compra->totalPagado();
                                        $saldoPendiente = $compra->saldoPendienteConImpuestos();
                                        $estadoPago = $compra->estadoPagoResumen();
                                    @endphp
                                    <tr>
                                        <td>{{ $compras->firstItem() + $loop->index }}</td>
                                        <td>
                                            @if ($canViewOrdenesCompra)
                                                <a href="{{ route('admin.ordenes-compra.show', $compra->id) }}">#{{ $compra->id }}</a>
                                            @else
                                                #{{ $compra->id }}
                                            @endif
                                        </td>
                                        <td>{{ $compra->deposito?->nombre ?? 'N/A' }}</td>
                                        <td>
                                            @if ($compra->pedidoArticulo)
                                                <a href="{{ route('admin.pedidos-articulos.show', $compra->pedidoArticulo->id) }}">
                                                    #{{ $compra->pedidoArticulo->id }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $compra->proveedorResumen() }}</td>
                                        <td>{{ $compra->usuario?->name ?? 'N/A' }}</td>
                                        <td>{{ $compra->fecha_compra?->format('d/m/Y') }}</td>
                                        <td>{{ $compra->formaPagoLabel() }}</td>
                                        <td>
                                            ${{ number_format($totalConImpuestos, 2, ',', '.') }}
                                            @if ($importeImpuestosProveedor > 0)
                                                <small class="d-block text-muted">Imp. ${{ number_format($importeImpuestosProveedor, 2, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            ${{ number_format($totalPagado, 2, ',', '.') }}
                                            @if ($saldoPendiente > 0)
                                                <small class="d-block text-muted">Saldo ${{ number_format($saldoPendiente, 2, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoPago['class'] }}">
                                                {{ $estadoPago['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$compra->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$compra->estado] ?? ucfirst((string) $compra->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $compra->notas ?: '-' }}</td>
                                        @if ($showOrdenCompraActions)
                                        <td class="text-end">
                                            @if ($canViewOrdenesCompra)
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="printCompra('printCompra-{{ $compra->id }}')" title="Imprimir">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            @endif
                                            @if ($canSendOrdenesCompraMail)
                                            <form action="{{ route('admin.ordenes-compra.mail', $compra->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirmFormSubmit(this, 'Enviar la orden de compra #{{ $compra->id }} por mail al proveedor?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" title="Enviar por mail">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @if ($canViewOrdenesCompra)
                                            <a href="{{ route('admin.ordenes-compra.show', $compra->id) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif
                                            @if ($canEditOrdenesCompra)
                                            <a href="{{ route('admin.ordenes-compra.pagos.create', $compra->id) }}" class="btn btn-sm btn-warning" title="Registrar pago">
                                                <i class="bi bi-cash-coin"></i>
                                            </a>
                                            @endif
                                            @if ($canEditOrdenesCompra)
                                            <a href="{{ route('admin.ordenes-compra.edit', $compra->id) }}" class="btn btn-sm btn-success" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @endif
                                            @if ($canDeleteOrdenesCompra)
                                            <form action="{{ route('admin.ordenes-compra.destroy', $compra->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar la orden de compra #{{ $compra->id }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showOrdenCompraActions ? 14 : 13 }}" class="text-center text-muted py-4">
                                            No hay ordenes de compra registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($compras->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $compras->firstItem() }} a {{ $compras->lastItem() }} de
                                {{ $compras->total() }} registros
                            </small>
                            <div>
                                {{ $compras->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($compras as $compra)
        @php
            $printImporteImpuestosProveedor = $compra->importeImpuestosProveedor();
            $printTotalConImpuestos = $compra->totalConImpuestos();
            $printSaldoPendiente = $compra->saldoPendienteConImpuestos();
        @endphp
        <div id="printCompra-{{ $compra->id }}" class="d-none">
            <div class="print-header">
                <div>
                    <h1>Orden de compra #{{ $compra->id }}</h1>
                    <p>Detalle general de la orden de compra</p>
                </div>
                <div class="print-status">
                    {{ $estadoLabels[$compra->estado] ?? ucfirst((string) $compra->estado) }}
                </div>
            </div>
            <div class="print-summary-grid">
                <div class="print-summary-item">
                    <span>Deposito</span>
                    <strong>{{ $compra->deposito?->nombre ?? 'N/A' }}</strong>
                </div>
                <div class="print-summary-item">
                    <span>Proveedor</span>
                    <strong>{{ $compra->proveedorResumen() }}</strong>
                </div>
                <div class="print-summary-item">
                    <span>Pedido de articulos</span>
                    <strong>{{ $compra->pedidoArticulo ? 'Pedido #' . $compra->pedidoArticulo->id : '-' }}</strong>
                </div>
                <div class="print-summary-item">
                    <span>Usuario</span>
                    <strong>{{ $compra->usuario?->name ?? 'N/A' }}</strong>
                </div>
                <div class="print-summary-item">
                    <span>Fecha</span>
                    <strong>{{ $compra->fecha_compra?->format('d/m/Y') }}</strong>
                </div>
                <div class="print-summary-item">
                    <span>Forma de pago</span>
                    <strong>{{ $compra->formaPagoLabel() }}</strong>
                </div>
                <div class="print-summary-item">
                    <span>Total</span>
                    <strong>${{ number_format($printTotalConImpuestos, 2, ',', '.') }}</strong>
                    <small>Base ${{ number_format((float) $compra->total_compra, 2, ',', '.') }} / Imp. ${{ number_format($printImporteImpuestosProveedor, 2, ',', '.') }}</small>
                </div>
                <div class="print-summary-item">
                    <span>Pagado</span>
                    <strong>${{ number_format($compra->totalPagado(), 2, ',', '.') }}</strong>
                    <small>Saldo ${{ number_format($printSaldoPendiente, 2, ',', '.') }}</small>
                </div>
                <div class="print-summary-item print-summary-wide">
                    <span>Datos de pago</span>
                    <strong>{{ $compra->datos_pago ?: '-' }}</strong>
                </div>
                <div class="print-summary-item print-summary-wide">
                    <span>Notas</span>
                    <strong>{{ $compra->notas ?: '-' }}</strong>
                </div>
            </div>
            <h2>Detalle</h2>
            <table>
                <thead>
                    <tr>
                        <th>Articulo</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($compra->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>${{ number_format((float) $detalle->precio_compra_unidad, 2, ',', '.') }}</td>
                            <td>${{ number_format((float) $detalle->precio_compra_unidad * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No hay detalles cargados para esta orden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <p class="print-tax-note">Importes expresados sin impuestos.</p>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script>
        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadCSVFromTable(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const csvRows = [];
            const headers = Array.from(table.querySelectorAll('thead th'))
                .map(header => '"' + header.innerText.trim().replace(/"/g, '""') + '"');
            csvRows.push(headers.join(','));

            Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
                const cols = Array.from(row.querySelectorAll('td'))
                    .map(td => '"' + td.innerText.trim().replace(/"/g, '""') + '"');
                csvRows.push(cols.join(','));
            });

            downloadCSV(csvRows.join('\n'), filename);
        }

        function exportTableToExcel(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8" /></head><body>${table.outerHTML}</body></html>`;
            const uri = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.href = uri;
            link.download = filename + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function createPDF(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}h1{text-align:center;margin-bottom:1rem;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>' + filename + '</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write('<h1>' + filename.replace(/_/g, ' ') + '</h1>');
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

        function printTable(tableId) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Imprimir</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

        function printCompra(elementId) {
            const content = document.getElementById(elementId);
            if (!content) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:24px;color:#1f2937;}h1{font-size:24px;margin:0 0 4px;}h2{font-size:18px;margin:22px 0 10px;}.print-header{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;border-bottom:2px solid #111827;padding-bottom:12px;margin-bottom:14px;}.print-header p{margin:0;color:#6b7280;}.print-status{border:1px solid #d1d5db;border-radius:4px;padding:6px 10px;font-weight:700;text-transform:uppercase;font-size:12px;}.print-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px 14px;margin-bottom:16px;}.print-summary-item{border-bottom:1px solid #e5e7eb;padding-bottom:7px;min-width:0;}.print-summary-item span{display:block;color:#6b7280;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:3px;}.print-summary-item strong{display:block;font-size:13px;overflow-wrap:anywhere;}.print-summary-item small,.print-tax-note{display:block;margin-top:4px;color:#6b7280;font-size:11px;font-weight:700;}.print-tax-note{text-align:right;}.print-summary-wide{grid-column:span 2;}table{width:100%;border-collapse:collapse;font-size:12px;}th,td{border:1px solid #d1d5db;padding:8px;text-align:left;}th{background:#f3f4f6;font-weight:700;}@media print{body{padding:0;}.print-summary-grid{grid-template-columns:repeat(4,minmax(0,1fr));}}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Orden de compra</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(content.innerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }
    </script>
@endpush
