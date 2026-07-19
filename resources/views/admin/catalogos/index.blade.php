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

@section('content')
    @php
        $permissionPrefix = \Illuminate\Support\Str::after($routePrefix, 'admin.');
        $canCreate = auth()->user()?->can($permissionPrefix . '.crear');
        $canEdit = auth()->user()?->can($permissionPrefix . '.editar');
        $canDelete = auth()->user()?->can($permissionPrefix . '.eliminar');
        $showActions = $canEdit || $canDelete;
    @endphp

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>{{ $title }}</h3>
                <p class="text-subtitle text-muted">{{ $subtitle }}</p>
            </div>
            @if ($canCreate)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $createModalId }}">
                    <i class="bi bi-plus-circle"></i> Nuevo {{ $entityName }}
                </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de {{ $entityNamePlural }} registrados</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route($routePrefix . '.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="search" class="form-label mb-1">Buscar {{ $entityName }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre de {{ $entityName }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'catalogos_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'catalogos_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'catalogos_registrados')">
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
                            Se encontraron {{ $items->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    @if ($showActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td>{{ $items->firstItem() + $loop->index }}</td>
                                        <td>{{ $item->nombre }}</td>
                                        @if ($showActions)
                                            <td class="text-end">
                                                @if ($canEdit)
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#{{ $editModalPrefix }}-{{ $item->id }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                @endif

                                                @if ($canDelete)
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#{{ $deleteModalPrefix }}-{{ $item->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showActions ? 3 : 2 }}" class="text-center text-muted py-4">
                                            No hay {{ $entityNamePlural }} registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($items->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $items->firstItem() }} a {{ $items->lastItem() }} de
                                {{ $items->total() }} registros
                            </small>
                            <div>
                                {{ $items->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @if ($canCreate)
        <div class="modal fade" id="{{ $createModalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route($routePrefix . '.store') }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" style="color: white">Crear {{ $entityName }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tags-fill"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control"
                                    value="{{ old('nombre') }}" placeholder="Nombre de {{ $entityName }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @foreach ($items as $item)
        @if ($canEdit)
            <div class="modal fade" id="{{ $editModalPrefix }}-{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route($routePrefix . '.update', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar {{ $entityName }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre-{{ $item->id }}" class="form-label">Nombre (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tags-fill"></i></span>
                                <input type="text" name="nombre" id="nombre-{{ $item->id }}" class="form-control"
                                    value="{{ session('open_modal') === $editModalPrefix . '-' . $item->id ? old('nombre', $item->nombre) : $item->nombre }}"
                                    placeholder="Nombre de {{ $entityName }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
            </div>
        @endif

        @if ($canDelete)
            <div class="modal fade" id="{{ $deleteModalPrefix }}-{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route($routePrefix . '.destroy', $item->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar {{ $entityName }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">
                            ¿Está seguro de eliminar {{ $entityName }} <strong>{{ $item->nombre }}</strong>?
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

        function exportTableToCSV(tableId, filename) {
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

            const newWindow = window.open('', '_blank');
            const style = '<style>table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            newWindow.document.write('<html><head><title>Imprimir tabla</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader() + table.outerHTML + '</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }
    </script>
@endpush
