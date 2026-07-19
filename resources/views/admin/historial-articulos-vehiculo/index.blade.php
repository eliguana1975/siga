@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Historial de articulos por vehiculo</h3>
                <p class="text-subtitle text-muted">
                    Consulta los articulos cargados en ordenes de trabajo por interno, fecha y tipo de articulo.
                </p>
            </div>
            <button type="button" class="btn btn-outline-secondary" onclick="printHistorialArticulos()">
                <i class="bi bi-printer"></i> Imprimir historial
            </button>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Filtros de consulta</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.historial-articulos-vehiculo.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-4">
                                <label for="flota_id" class="form-label">Vehiculo</label>
                                <select name="flota_id" id="flota_id" class="form-select js-select2" data-placeholder="Seleccione vehiculo">
                                    <option value="">Todos los vehiculos</option>
                                    @foreach ($flotas as $flota)
                                        <option value="{{ $flota->id }}" @selected((string) $filters['flota_id'] === (string) $flota->id)>
                                            {{ $flota->nro_interno }} - {{ $flota->dominio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="fecha_desde" class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                                    value="{{ $filters['fecha_desde'] }}">
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="fecha_hasta" class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                                    value="{{ $filters['fecha_hasta'] }}">
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="categoria_id" class="form-label">Tipo de articulo</label>
                                <select name="categoria_id" id="categoria_id" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" @selected((string) $filters['categoria_id'] === (string) $categoria->id)>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.historial-articulos-vehiculo.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h4 class="card-title mb-0">Articulos agregados</h4>
                    <span class="badge bg-light-info">
                        Total: {{ number_format((int) $totalCantidad, 0, ',', '.') }} unidad(es)
                    </span>
                </div>
                <div class="card-body">
                    <div id="historialPrintArea">
                        <div class="d-none print-title">
                            <h2>Historial de articulos por vehiculo</h2>
                            <p>Consulta generada el {{ now()->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Orden</th>
                                        <th>Interno</th>
                                        <th>Dominio</th>
                                        <th>Articulo</th>
                                        <th>Tipo</th>
                                        <th>Unidad</th>
                                        <th>Cantidad</th>
                                        <th>Posicion</th>
                                        <th>Nro control sacada</th>
                                        <th>Nro control colocada</th>
                                        <th>Valor unit.</th>
                                        <th>Total</th>
                                        <th>Empleado</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($historial as $detalle)
                                        @php
                                            $empleadoEjecutor = $detalle->ordenTrabajo?->reparador ?: $detalle->ordenTrabajo?->empleado;
                                        @endphp
                                        <tr>
                                            <td>{{ $detalle->ordenTrabajo?->fecha_orden?->format('d/m/Y') }}</td>
                                            <td>#{{ $detalle->ordenTrabajo?->id }}</td>
                                            <td>{{ $detalle->ordenTrabajo?->flota?->nro_interno ?? '-' }}</td>
                                            <td>{{ $detalle->ordenTrabajo?->flota?->dominio ?? '-' }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $detalle->articulo?->nombre ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $detalle->articulo?->codigo_producto ?: '-' }}</small>
                                            </td>
                                            <td>{{ $detalle->articulo?->categoria?->nombre ?? '-' }}</td>
                                            <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                                            <td>{{ number_format((int) $detalle->cantidad, 0, ',', '.') }}</td>
                                            <td>{{ $detalle->detalleCambioCubierta?->posicion ?? '-' }}</td>
                                            <td>{{ $detalle->detalleCambioCubierta?->nro_cubierta_sacada ?? '-' }}</td>
                                            <td>{{ $detalle->detalleCambioCubierta?->nro_cubierta_colocada ?? '-' }}</td>
                                            <td>${{ number_format((float) $detalle->valor_unitario, 2, ',', '.') }}</td>
                                            <td>${{ number_format((float) $detalle->valor_unitario * (int) $detalle->cantidad, 2, ',', '.') }}</td>
                                            <td>
                                                {{ trim(($empleadoEjecutor?->apellidos ?? '') . ' ' . ($empleadoEjecutor?->nombres ?? '')) ?: '-' }}
                                            </td>
                                            <td>{{ $detalle->observaciones ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="text-center text-muted py-4">
                                                @if ($hasSearched)
                                                    No hay articulos agregados para los filtros seleccionados.
                                                @else
                                                    Seleccione los filtros y presione Buscar para cargar el historial.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($historial->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $historial->firstItem() }} a {{ $historial->lastItem() }} de {{ $historial->total() }} registros
                            </small>
                            <div>{{ $historial->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function printHistorialArticulos() {
            const content = document.getElementById('historialPrintArea');

            if (!content) {
                return;
            }

            const style = `
                <style>
                    body{font-family:Arial,Helvetica,sans-serif;padding:20px;color:#20222f;}
                    h2{margin:0 0 4px;color:#25396f;}
                    p{margin:0 0 16px;color:#6c757d;}
                    table{width:100%;border-collapse:collapse;font-size:12px;}
                    th,td{border:1px solid #dee2e6;padding:7px;text-align:left;vertical-align:top;}
                    th{background:#f2f4fb;color:#25396f;}
                    .d-none{display:block !important;}
                    .text-muted{color:#6c757d;}
                    .fw-semibold{font-weight:700;}
                    @media print{@page{size:A4 landscape;margin:10mm;}body{padding:0;}}
                </style>
            `;
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Historial de articulos</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            printWindow.document.write(content.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }
    </script>
@endpush
