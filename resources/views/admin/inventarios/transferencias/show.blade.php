@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Transferencia #{{ $transferencia->id }}</h3>
                <p class="text-subtitle text-muted">Detalle del movimiento entre depositos.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" onclick="printTransferenciaDeposito('printTransferenciaDeposito-{{ $transferencia->id }}')">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="{{ route('admin.inventarios.transferencias.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <small class="text-muted fw-semibold">Origen</small>
                            <div class="fw-semibold">{{ $transferencia->depositoOrigen?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted fw-semibold">Destino</small>
                            <div class="fw-semibold">{{ $transferencia->depositoDestino?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted fw-semibold">Usuario</small>
                            <div class="fw-semibold">{{ $transferencia->usuario?->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted fw-semibold">Fecha</small>
                            <div class="fw-semibold">{{ $transferencia->fecha_transferencia?->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted fw-semibold">Observaciones</small>
                            <div class="fw-semibold">{{ $transferencia->observaciones ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Detalle de articulos transferidos</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Codigo</th>
                                    <th>Unidad</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transferencia->detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->articulo?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $detalle->articulo?->codigo_producto ?? '-' }}</td>
                                        <td>{{ $detalle->articulo?->unidadMedida?->nombre ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No hay articulos cargados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('admin.inventarios.transferencias.partials.print-planilla', ['transferencia' => $transferencia])
@endsection

@include('admin.inventarios.transferencias.partials.print-script')
