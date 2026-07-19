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
    </style>
@endpush

@include('admin.inventarios.partials.label-print-script')
@php
    $canCreateArticulos = auth()->user()?->can('articulos.crear');
    $canEditArticulos = auth()->user()?->can('articulos.editar');
    $canDeleteArticulos = auth()->user()?->can('articulos.eliminar');
    $canPrintInventarioLabels = auth()->user()?->can('inventarios.etiquetas');
    $showArticuloActions = auth()->user()?->can('articulos.ver') || $canPrintInventarioLabels || $canEditArticulos || $canDeleteArticulos;
    $articulosListRoute = request()->routeIs('admin.articulos.listado') ? route('admin.articulos.listado') : route('admin.articulos.index');
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestión de Artículos</h3>
                <p class="text-subtitle text-muted">
                    Administra los artículos del inventario, categorías, unidades de medida y stock.
                </p>
            </div>
            @if ($canCreateArticulos)
             <a href="{{ route('admin.articulos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo artículo
            </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de artículos registrados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ $articulosListRoute }}" class="mb-3" id="articulosSearchForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar artículo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre, código o ubicación del artículo">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ $articulosListRoute }}"
                                    class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'articulos_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'articulos_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'articulos_registrados')">
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
                            Se encontraron {{ $articulos->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th>Unidad</th>
                                    <th>Ubicación del artículo</th>
                                    <th>Stock Mín</th>
                                    <th>Stock Máx</th>
                                    <th>Estado</th>
                                    @if ($showArticuloActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($articulos as $articulo)
                                    <tr>
                                        <td>{{ $articulo->id }}</td>
                                        <td>{{ $articulo->nombre }}</td>
                                        <td>{{ $articulo->codigo_producto ?? 'Sin código' }}</td>
                                        <td>{{ $articulo->categoria->nombre ?? 'N/A' }}</td>
                                        <td>{{ $articulo->unidadMedida->nombre ?? 'N/A' }}</td>
                                        <td>
                                            @if ($articulo->pasillo || $articulo->estanteria || $articulo->casillero)
                                                <span class="text-nowrap">
                                                    {{ collect([
                                                        $articulo->pasillo ? 'P: ' . $articulo->pasillo : null,
                                                        $articulo->estanteria ? 'E: ' . $articulo->estanteria : null,
                                                        $articulo->casillero ? 'C: ' . $articulo->casillero : null,
                                                    ])->filter()->implode(' / ') }}
                                                </span>
                                            @else
                                                <span class="text-muted">Sin ubicacion</span>
                                            @endif
                                        </td>
                                        <td>{{ $articulo->stock_minimo }}</td>
                                        <td>{{ $articulo->stock_maximo }}</td>
                                        <td>
                                            @if ($articulo->estado_item === 'activo')
                                                <span class="badge bg-light-success">Activo</span>
                                            @else
                                                <span class="badge bg-light-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        @if ($showArticuloActions)
                                            <td class="text-end">
                                                @php
                                                    $inventarioEtiqueta = $articulo->inventarios->first();
                                                @endphp
                                                @can('articulos.ver')
                                                    <a href="{{ route('admin.articulos.show', $articulo->id) }}" class="btn btn-sm btn-info me-1" title="Ver detalle">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan

                                                @if ($canPrintInventarioLabels)
                                                    @if ($inventarioEtiqueta)
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary me-1"
                                                            title="Imprimir etiqueta"
                                                            data-label-print-url="{{ route('admin.inventarios.etiqueta', $inventarioEtiqueta->id) }}">
                                                            <i class="bi bi-qr-code"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-light-secondary me-1" title="Sin stock para generar etiqueta" disabled>
                                                            <i class="bi bi-qr-code"></i>
                                                        </button>
                                                    @endif
                                                @endif

                                                @if ($canEditArticulos)
                                                    <a href="{{ route('admin.articulos.edit', $articulo->id) }}" class="btn btn-sm btn-success me-1" title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @endif

                                                @if ($canDeleteArticulos)
                                                    <form action="{{ route('admin.articulos.destroy', $articulo->id) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar el articulo {{ addslashes($articulo->nombre) }}?');">
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
                                        <td colspan="{{ $showArticuloActions ? 10 : 9 }}" class="text-center text-muted py-4">No hay artículos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($articulos->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $articulos->firstItem() }} a {{ $articulos->lastItem() }} de
                                {{ $articulos->total() }} registros
                            </small>
                            <div>
                                {{ $articulos->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

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
    </script>
@endpush

