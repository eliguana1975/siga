@extends('layouts.admin')

@php
    $estadoLabels = [
        'aprobada' => 'Aprobada',
        'recibido' => 'Recibido',
    ];

    $estadoBadges = [
        'aprobada' => 'bg-light-primary',
        'recibido' => 'bg-light-success',
    ];

    $canViewCompras = auth()->user()?->can('compras.ver');
    $canDeleteCompras = auth()->user()?->can('compras.eliminar');
    $canViewOrdenesCompra = auth()->user()?->can('ordenes-compra.ver');
    $showCompraActions = $canViewCompras || $canDeleteCompras || $canViewOrdenesCompra;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Compras aprobadas y recibidas</h3>
                <p class="text-subtitle text-muted">
                    Consulta las ordenes de compra que ya fueron aprobadas o recibidas.
                </p>
            </div>
            @if ($canViewOrdenesCompra)
            <a href="{{ route('admin.ordenes-compra.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Ordenes de compra
            </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de compras aprobadas y recibidas</h4>
                </div>
                <div class="card-body">
                    @if (($ordenesPendientes ?? 0) > 0)
                        <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2 mb-3" role="alert">
                            <span>
                                Hay {{ $ordenesPendientes }} orden(es) de compra pendiente(s). Este listado muestra compras aprobadas y recibidas.
                            </span>
                            <a href="{{ route('admin.ordenes-compra.index', ['search' => 'pendiente']) }}" class="btn btn-sm btn-primary">
                                Ver ordenes pendientes
                            </a>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.compras.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar compra</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Numero de orden, proveedor, deposito, usuario, pedido o notas">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.compras.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'compras_aprobadas')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'compras_aprobadas')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'compras_aprobadas')">
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
                                    <th>Total</th>
                                    <th>Forma de pago</th>
                                    <th>Pagado</th>
                                    <th>Estado</th>
                                    <th>Notas</th>
                                    @if ($showCompraActions)
                                        <th class="text-end" style="width: 140px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compras as $compra)
                                    <tr>
                                        <td>{{ $compras->firstItem() + $loop->index }}</td>
                                        <td>#{{ $compra->id }}</td>
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
                                        <td>${{ number_format((float) $compra->total_compra, 2, ',', '.') }}</td>
                                        <td>{{ $compra->formaPagoLabel() }}</td>
                                        <td>
                                            ${{ number_format($compra->totalPagado(), 2, ',', '.') }}
                                            @if ($compra->saldoPendiente() > 0)
                                                <small class="d-block text-muted">Saldo ${{ number_format($compra->saldoPendiente(), 2, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$compra->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$compra->estado] ?? ucfirst((string) $compra->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $compra->notas ?: '-' }}</td>
                                        @if ($showCompraActions)
                                        <td class="text-end">
                                            @if ($canViewCompras)
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="printCompra('printCompra-{{ $compra->id }}')" title="Imprimir">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            @endif
                                            @if ($canViewOrdenesCompra)
                                            <a href="{{ route('admin.ordenes-compra.show', $compra->id) }}" class="btn btn-sm btn-info" title="Ver orden">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif
                                            @if ($canDeleteCompras)
                                            <form method="POST" action="{{ route('admin.compras.destroy', $compra->id) }}" class="d-inline"
                                                onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar la compra aprobada #{{ $compra->id }}?');">
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
                                        <td colspan="{{ $showCompraActions ? 13 : 12 }}" class="text-center text-muted py-4">
                                            No hay compras aprobadas o recibidas registradas.
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
        <div id="printCompra-{{ $compra->id }}" class="d-none">
            <h1>Compra aprobada #{{ $compra->id }}</h1>
            <table>
                <tr>
                    <th>Deposito</th>
                    <td>{{ $compra->deposito?->nombre ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Pedido</th>
                    <td>{{ $compra->pedidoArticulo ? '#' . $compra->pedidoArticulo->id : '-' }}</td>
                </tr>
                <tr>
                    <th>Proveedor</th>
                    <td>{{ $compra->proveedorResumen() }}</td>
                </tr>
                <tr>
                    <th>Usuario</th>
                    <td>{{ $compra->usuario?->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <td>{{ $compra->fecha_compra?->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td>${{ number_format((float) $compra->total_compra, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Forma de pago</th>
                    <td>{{ $compra->formaPagoLabel() }}</td>
                </tr>
                <tr>
                    <th>Datos de pago</th>
                    <td>{{ $compra->datos_pago ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Pagado</th>
                    <td>${{ number_format($compra->totalPagado(), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Saldo</th>
                    <td>${{ number_format($compra->saldoPendiente(), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td>Aprobada</td>
                </tr>
                <tr>
                    <th>Notas</th>
                    <td>{{ $compra->notas ?: '-' }}</td>
                </tr>
            </table>
            <h2>Detalle</h2>
            <table>
                <thead>
                    <tr>
                        <th>Articulo</th>
                        <th>Proveedor</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($compra->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                            <td>{{ $detalle->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>${{ number_format((float) $detalle->precio_compra_unidad, 2, ',', '.') }}</td>
                            <td>${{ number_format((float) $detalle->precio_compra_unidad * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No hay detalles cargados para esta compra.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}h1{text-align:center;margin-bottom:1rem;}table{width:100%;border-collapse:collapse;margin-bottom:1rem;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{width:180px;background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Compra aprobada</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(content.innerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

    </script>
@endpush
