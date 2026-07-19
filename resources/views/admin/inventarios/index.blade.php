@extends('layouts.admin')

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

        .inventory-metrics > [class*="col-"] {
            display: flex;
        }

        .inventory-metric {
            display: flex;
            width: 100%;
            height: 178px !important;
            min-height: 178px !important;
            margin-bottom: 0;
        }

        .inventory-metric .card-body {
            display: flex !important;
            height: 100%;
            width: 100%;
        }

        .inventory-metric .metric-content {
            min-width: 0;
        }

        .inventory-metric .metric-action {
            min-height: 32px;
        }

        .inventory-metric small {
            display: block;
            line-height: 1.45;
            min-height: 42px;
        }

        .inventory-metric .metric-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: .5rem;
            font-size: 1.15rem;
            line-height: 1;
            text-align: center;
        }

        .inventory-metric .metric-icon i {
            display: block;
            line-height: 1;
        }

        #datatable .inventory-col-number {
            width: 48px;
            white-space: nowrap;
        }

        #datatable .inventory-col-stock {
            width: 76px;
            white-space: nowrap;
            text-align: center;
        }
    </style>
@endpush

@include('admin.inventarios.partials.label-print-script')

@php
    $ubicacionLabel = function ($articulo) {
        if (! $articulo) {
            return 'Sin ubicacion';
        }

        return collect([
            $articulo->pasillo ? 'P ' . $articulo->pasillo : null,
            $articulo->estanteria ? 'E ' . $articulo->estanteria : null,
            $articulo->casillero ? 'C ' . $articulo->casillero : null,
        ])->filter()->implode(' / ') ?: 'Sin detalle';
    };

    $canEditInventarios = auth()->user()?->can('inventarios.editar');
    $canDeleteInventarios = auth()->user()?->can('inventarios.eliminar');
    $canPrintInventarioLabels = auth()->user()?->can('inventarios.etiquetas');
    $showInventarioActions = $canEditInventarios || $canDeleteInventarios || $canPrintInventarioLabels;
    $canCreateTransferencias = auth()->user()?->can('inventario-transferencias.crear');
    $canViewEntradasPendientes = auth()->user()?->can('entradas.ver');
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestion de Inventarios</h3>
                <p class="text-subtitle text-muted">
                    Administra el stock por articulo, deposito y ubicacion fisica.
                </p>
            </div>
            @if ($canCreateTransferencias)
            <a href="{{ route('admin.inventarios.transferencias.create') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-right"></i> Nueva transferencia
            </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="row g-3 align-items-stretch inventory-metrics">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card inventory-metric">
                        <div class="card-body d-flex justify-content-between align-items-start gap-3">
                            <div class="metric-content d-flex flex-column h-100">
                                <p class="text-muted mb-1">Articulos registrados</p>
                                <h3 class="mb-0">{{ number_format($resumen['total_articulos'], 0, ',', '.') }}</h3>
                                <small class="text-muted">{{ number_format($resumen['articulos_en_inventario'], 0, ',', '.') }} con inventario</small>
                                <div class="metric-action mt-auto"></div>
                            </div>
                            <span class="metric-icon bg-light-primary"><i class="bi bi-box-seam"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card inventory-metric">
                        <div class="card-body d-flex justify-content-between align-items-start gap-3">
                            <div class="metric-content d-flex flex-column h-100">
                                <p class="text-muted mb-1">Depositos registrados</p>
                                <h3 class="mb-0">{{ number_format($resumen['total_depositos'], 0, ',', '.') }}</h3>
                                <small class="text-muted">Espacios disponibles</small>
                                <div class="metric-action mt-auto"></div>
                            </div>
                            <span class="metric-icon bg-light-secondary"><i class="bi bi-building"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card inventory-metric">
                        <div class="card-body d-flex justify-content-between align-items-start gap-3">
                            <div class="metric-content d-flex flex-column h-100">
                                <p class="text-muted mb-1">Cantidad total</p>
                                <h3 class="mb-0">{{ number_format($resumen['cantidad_total'], 0, ',', '.') }}</h3>
                                <small class="text-muted">Unidades en stock</small>
                                <div class="metric-action mt-auto"></div>
                            </div>
                            <span class="metric-icon bg-light-info"><i class="bi bi-123"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card inventory-metric">
                        <div class="card-body d-flex justify-content-between align-items-start gap-3">
                            <div class="metric-content d-flex flex-column h-100">
                                <p class="text-muted mb-1">Stock minimo</p>
                                <h3 class="mb-0">{{ number_format($resumen['stock_minimo'], 0, ',', '.') }}</h3>
                                <small class="text-muted">Registros en minimo o menos</small>
                                <div class="metric-action mt-auto">
                                    @can('inventarios.ver')
                                        <a href="{{ route('admin.inventarios.bajo-stock') }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-eye"></i> Ver bajo stock
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <span class="metric-icon bg-light-warning"><i class="bi bi-exclamation-triangle"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card inventory-metric">
                        <div class="card-body d-flex justify-content-between align-items-start gap-3">
                            <div class="metric-content d-flex flex-column h-100">
                                <p class="text-muted mb-1">Sin stock</p>
                                <h3 class="mb-0">{{ number_format($resumen['sin_stock'], 0, ',', '.') }}</h3>
                                <small class="text-muted">{{ number_format($resumen['sobre_stock_maximo'], 0, ',', '.') }} sobre stock maximo</small>
                                <div class="metric-action mt-auto">
                                    @can('inventarios.ver')
                                        <a href="{{ route('admin.inventarios.sin-stock') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-eye"></i> Ver sin stock
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <span class="metric-icon bg-light-danger"><i class="bi bi-slash-circle"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card inventory-metric">
                        <div class="card-body d-flex justify-content-between align-items-start gap-3">
                            <div class="metric-content d-flex flex-column h-100">
                                <p class="text-muted mb-1">Pendientes de entrega</p>
                                <h3 class="mb-0">{{ number_format($resumen['pendientes_entrega'], 0, ',', '.') }}</h3>
                                <small class="text-muted">Unidades pendientes por recibir</small>
                                <div class="metric-action mt-auto">
                                    @if ($canViewEntradasPendientes)
                                        <a href="{{ route('admin.entradas.pendientes') }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Ver pendientes
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <span class="metric-icon bg-light-info"><i class="bi bi-truck"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-2">
                            <div>
                                <p class="text-muted mb-1">Valor estimado del inventario</p>
                                <h4 class="mb-0">${{ number_format($resumen['valor_total'], 2, ',', '.') }}</h4>
                            </div>
                            <div class="text-md-end">
                                <p class="text-muted mb-1">Estado general</p>
                                @if ($resumen['stock_minimo'] > 0)
                                    <span class="badge bg-light-warning">Revisar stock minimo</span>
                                @else
                                    <span class="badge bg-light-success">Stock dentro de parametros</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de inventarios registrados</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.inventarios.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar inventario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Articulo, codigo, deposito o ubicacion">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.inventarios.index') }}"
                                    class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'inventarios_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'inventarios_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'inventarios_registrados')">
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
                            Se encontraron {{ $inventarios->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="inventory-col-number">#</th>
                                    <th>Articulo</th>
                                    <th>Deposito</th>
                                    <th>Ubicacion fisica</th>
                                    <th class="inventory-col-stock">Stock</th>
                                    @if ($showInventarioActions)
                                        <th class="text-end" style="width: 190px;" data-export-ignore="true">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventarios as $inventario)
                                    <tr>
                                        <td class="inventory-col-number">{{ $inventarios->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $inventario->articulo->nombre ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $inventario->articulo->codigo_producto ?? 'Sin codigo' }}</small>
                                        </td>
                                        <td>{{ $inventario->deposito->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <div>{{ $ubicacionLabel($inventario->articulo) }}</div>
                                        </td>
                                        <td class="inventory-col-stock">{{ $inventario->cantidad }}</td>
                                        @if ($showInventarioActions)
                                        <td class="text-end" data-export-ignore="true">
                                            @if ($canPrintInventarioLabels)
                                            <button type="button"
                                                class="btn btn-sm btn-info"
                                                title="Imprimir etiqueta"
                                                data-label-print-url="{{ route('admin.inventarios.etiqueta', $inventario->id) }}">
                                                <i class="bi bi-qr-code"></i>
                                            </button>
                                            @endif

                                            @if ($canEditInventarios)
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editInventarioModal-{{ $inventario->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @endif

                                            @if ($canDeleteInventarios)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteInventarioModal-{{ $inventario->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showInventarioActions ? 6 : 5 }}" class="text-center text-muted py-4">No hay inventarios registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($inventarios->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $inventarios->firstItem() }} a {{ $inventarios->lastItem() }} de
                                {{ $inventarios->total() }} registros
                            </small>
                            <div>
                                {{ $inventarios->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($inventarios as $inventario)
        @if ($canEditInventarios)
        <div class="modal fade" id="editInventarioModal-{{ $inventario->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <form class="modal-content" method="POST" action="{{ route('admin.inventarios.update', $inventario->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar inventario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @include('admin.inventarios.partials.form', [
                            'inventario' => $inventario,
                            'modalId' => 'editInventarioModal-' . $inventario->id,
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if ($canDeleteInventarios)
        <div class="modal fade" id="deleteInventarioModal-{{ $inventario->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.inventarios.destroy', $inventario->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar inventario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">
                            Esta seguro de eliminar el inventario del articulo
                            <strong>{{ $inventario->articulo->nombre ?? 'N/A' }}</strong>?
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endforeach
@endsection

@push('scripts')
    <script data-open-modal="{{ session('open_modal') }}">
        (function() {
            const openModalId = document.currentScript.dataset.openModal;

            if (!openModalId || typeof bootstrap === 'undefined') {
                return;
            }

            const modalElement = document.getElementById(openModalId);

            if (!modalElement) {
                return;
            }

            new bootstrap.Modal(modalElement).show();
        })();

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

        function inventoryExportTable(tableId) {
            const table = document.getElementById(tableId);

            if (!table) {
                return null;
            }

            const clone = table.cloneNode(true);
            const ignoredIndexes = Array.from(clone.querySelectorAll('thead th')).reduce((indexes, header, index) => {
                if (header.dataset.exportIgnore === 'true') {
                    indexes.push(index);
                }

                return indexes;
            }, []);

            clone.querySelectorAll('tr').forEach(row => {
                ignoredIndexes.slice().reverse().forEach(index => {
                    row.children[index]?.remove();
                });
            });

            clone.querySelectorAll('button, .btn').forEach(element => element.remove());
            clone.querySelectorAll('th, td').forEach(cell => {
                cell.classList.remove('text-end');
            });

            return clone;
        }

        function downloadCSVFromTable(tableId, filename) {
            const table = inventoryExportTable(tableId);
            if (!table) {
                return;
            }

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const csvRows = [];

            const headers = Array.from(table.querySelectorAll('thead th')).map(header => '"' + header.innerText.trim().replace(/"/g, '""') + '"');
            csvRows.push(headers.join(','));

            rows.forEach(row => {
                const cols = Array.from(row.querySelectorAll('td')).map(td => '"' + td.innerText.trim().replace(/"/g, '""') + '"');
                csvRows.push(cols.join(','));
            });

            downloadCSV(csvRows.join('\n'), filename);
        }

        function exportTableToExcel(tableId, filename) {
            const table = inventoryExportTable(tableId);
            if (!table) {
                return;
            }

            const style = '<style>.inventory-col-number{width:42px}.inventory-col-stock{width:58px;text-align:center}</style>';
            const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8" />${style}</head><body>${table.outerHTML}</body></html>`;
            const uri = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.href = uri;
            link.download = filename + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function createPDF(tableId, filename) {
            const table = inventoryExportTable(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}h1{text-align:center;margin-bottom:1rem;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.5rem;text-align:left;}th{background:#f8f9fa;}.inventory-col-number{width:42px}.inventory-col-stock{width:58px;text-align:center}</style>';
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
            const table = inventoryExportTable(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.5rem;text-align:left;}th{background:#f8f9fa;}.inventory-col-number{width:42px}.inventory-col-stock{width:58px;text-align:center}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Imprimir</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }
    </script>
@endpush
