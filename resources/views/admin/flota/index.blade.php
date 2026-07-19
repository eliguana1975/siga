@extends('layouts.admin')

@php
    $estadoLabels = [
        'activo' => 'Activo',
        'baja' => 'Baja',
        'mantenimiento' => 'Mantenimiento',
    ];

    $estadoBadges = [
        'activo' => 'bg-light-success',
        'baja' => 'bg-light-secondary',
        'mantenimiento' => 'bg-light-warning',
    ];

    $seguroBadges = [
        'Activo' => 'bg-light-success',
        'Baja' => 'bg-light-secondary',
    ];

    $canCreateFlota = auth()->user()?->can('flota.crear');
    $canEditFlota = auth()->user()?->can('flota.editar');
    $canDeleteFlota = auth()->user()?->can('flota.eliminar');
    $canAssignFlotaService = auth()->user()?->can('flota-servicio-asignado.editar');
    $canViewFlotaRepuestos = auth()->user()?->can('flota-repuestos.ver');
    $showFlotaActions = $canEditFlota || $canDeleteFlota || $canAssignFlotaService || $canViewFlotaRepuestos;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Flota</h3>
                <p class="text-subtitle text-muted">Administra el registro de vehiculos y sus datos asociados.</p>
            </div>
            @if ($canCreateFlota)
                <a href="{{ route('admin.flota.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo registro
                </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de la flota</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.flota.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Buscar por interno, dominio, motor, chasis...">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.flota.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'flota_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'flota_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'flota_registrados')">
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
                            Se encontraron {{ $flotas->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Interno</th>
                                    <th>Dominio</th>
                                    <th>Tipo motor</th>
                                    <th>Marca</th>
                                    <th>Titular</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Seguro</th>
                                    @if ($showFlotaActions)
                                        <th class="text-end" style="width: 260px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flotas as $flota)
                                    <tr>
                                        <td>{{ $flotas->firstItem() + $loop->index }}</td>
                                        <td>{{ $flota->nro_interno }}</td>
                                        <td>{{ $flota->dominio }}</td>
                                        <td>{{ $flota->tipoMotor?->nombre }}</td>
                                        <td>{{ $flota->marcaCarroceria?->nombre }}</td>
                                        <td>{{ $flota->titular?->nombre }}</td>
                                        <td>{{ $flota->servicioAsignadoActual?->nombre ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$flota->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$flota->estado] ?? ucfirst((string) $flota->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $seguroBadges[$flota->estado_seguro] ?? 'bg-light-secondary' }}">
                                                {{ $flota->estado_seguro }}
                                            </span>
                                        </td>
                                        @if ($showFlotaActions)
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end flex-wrap gap-1">
                                                @if ($canEditFlota)
                                                @foreach ([
                                                    1 => $flota->foto_flota,
                                                    2 => $flota->foto_flota_2,
                                                    3 => $flota->foto_flota_3,
                                                    4 => $flota->foto_flota_4,
                                                ] as $fotoNumero => $fotoPath)
                                                    @if ($fotoPath)
                                                        <a href="{{ asset('storage/' . $fotoPath) }}"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            download
                                                            title="Descargar foto {{ $fotoNumero }}"
                                                            aria-label="Descargar foto {{ $fotoNumero }}">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            disabled
                                                            title="Foto {{ $fotoNumero }} no cargada"
                                                            aria-label="Foto {{ $fotoNumero }} no cargada">
                                                            <i class="bi bi-download"></i>
                                                        </button>
                                                    @endif
                                                @endforeach

                                                <a href="{{ route('admin.flota.edit', $flota->id) }}" class="btn btn-sm btn-success" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @endif

                                                @if ($canAssignFlotaService)
                                                <a href="{{ route('admin.flota.servicio-asignado.edit', $flota->id) }}" class="btn btn-sm btn-primary" title="Asignar servicio">
                                                    <i class="bi bi-diagram-3"></i>
                                                </a>
                                                @endif

                                                @if ($canViewFlotaRepuestos)
                                                    <a href="{{ route('admin.flota.repuestos.index', $flota->id) }}" class="btn btn-sm btn-info" title="Repuestos">
                                                        <i class="bi bi-tools"></i>
                                                    </a>
                                                @endif

                                                @if ($canDeleteFlota)
                                                <form action="{{ route('admin.flota.destroy', $flota->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar el vehiculo {{ addslashes($flota->nro_interno) }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showFlotaActions ? 10 : 9 }}" class="text-center text-muted py-4">
                                            No hay vehiculos de flota registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($flotas->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $flotas->firstItem() }} a {{ $flotas->lastItem() }} de {{ $flotas->total() }} registros
                            </small>
                            <div>
                                {{ $flotas->links('vendor.pagination.bootstrap-5-no-summary') }}
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

            const csvRows = [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(header => '"' + header.innerText.trim().replace(/"/g, '""') + '"');
            csvRows.push(headers.join(','));

            Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
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
