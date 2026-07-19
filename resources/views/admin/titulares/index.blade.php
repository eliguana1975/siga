@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Titulares</h3>
                <p class="text-subtitle text-muted">Administra los titulares disponibles para la flota del sistema.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTitularModal">
                <i class="bi bi-plus-circle"></i> Nuevo titular
            </button>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de titulares</h4>
                </div>
                <div class="card-body">
                    
                    <form method="GET" action="{{ route('admin.titulares.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="search" class="form-label mb-1">Buscar titular</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre, teléfono o email">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.titulares.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'titulares_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'titulares_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'titulares_registrados')">
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
                            Se encontraron {{ $titulares->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th class="text-end" style="width: 220px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($titulares as $titular)
                                    <tr>
                                        <td>{{ $titulares->firstItem() + $loop->index }}</td>
                                        <td>
                                            {{ $titular->nombre }}
                                            @if ($titular->es_empresa)
                                                <span class="badge bg-light-primary ms-1">Empresa</span>
                                            @endif
                                        </td>
                                        <td>{{ $titular->telefono }}</td>
                                        <td>{{ $titular->email }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editTitularModal-{{ $titular->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @unless ($titular->es_empresa)
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteTitularModal-{{ $titular->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No hay titulares registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($titulares->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $titulares->firstItem() }} a {{ $titulares->lastItem() }} de {{ $titulares->total() }} registros
                            </small>
                            <div>
                                {{ $titulares->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="createTitularModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('admin.titulares.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nuevo titular</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && session('open_modal') === 'createTitularModal')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="150">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($titulares as $titular)
        <div class="modal fade" id="editTitularModal-{{ $titular->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.titulares.update', $titular->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Editar titular</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any() && session('open_modal') === 'editTitularModal-' . $titular->id)
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                value="{{ session('open_modal') === 'editTitularModal-' . $titular->id ? old('nombre', $titular->nombre) : $titular->nombre }}"
                                maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2">{{ session('open_modal') === 'editTitularModal-' . $titular->id ? old('direccion', $titular->direccion) : $titular->direccion }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                value="{{ session('open_modal') === 'editTitularModal-' . $titular->id ? old('telefono', $titular->telefono) : $titular->telefono }}"
                                maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ session('open_modal') === 'editTitularModal-' . $titular->id ? old('email', $titular->email) : $titular->email }}"
                                maxlength="150">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="deleteTitularModal-{{ $titular->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.titulares.destroy', $titular->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Eliminar titular</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">¿Está seguro de eliminar el titular <strong>{{ $titular->nombre }}</strong>?</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
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
