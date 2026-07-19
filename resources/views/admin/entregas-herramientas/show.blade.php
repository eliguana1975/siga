@extends('layouts.admin')

@php
    $estadoLabels = [
        'entregada' => 'Entregada',
        'parcial' => 'Parcial',
        'devuelta' => 'Devuelta',
        'cancelada' => 'Cancelada',
        'rota' => 'Rota',
        'perdida' => 'Perdida',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Entrega de herramientas #{{ $entrega->id }}</h3>
                <p class="text-subtitle text-muted">Detalle de herramientas asignadas al empleado.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" onclick="printEntregaHerramienta('printEntregaHerramienta-{{ $entrega->id }}')">
                    <i class="bi bi-printer"></i> Planilla de firma
                </button>
                <a href="{{ route('admin.entregas-herramientas.index') }}" class="btn btn-light-secondary">
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
                            <small class="text-muted d-block">Empleado</small>
                            <strong>{{ trim(($entrega->empleado?->apellidos ?? '') . ' ' . ($entrega->empleado?->nombres ?? '')) ?: 'N/A' }}</strong>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Documento</small>
                            <strong>{{ $entrega->empleado?->numero_doc ?? '-' }}</strong>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Deposito</small>
                            <strong>{{ $entrega->deposito?->nombre ?? '-' }}</strong>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Fecha</small>
                            <strong>{{ $entrega->fecha_entrega?->format('d/m/Y') }}</strong>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Estado</small>
                            <span class="badge bg-light-primary">{{ $estadoLabels[$entrega->estado] ?? ucfirst((string) $entrega->estado) }}</span>
                        </div>
                        <div class="col-12 col-md-9">
                            <small class="text-muted d-block">Observaciones</small>
                            <span>{{ $entrega->observaciones ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Herramientas entregadas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Herramienta</th>
                                    <th>Entregada</th>
                                    <th>Devuelta</th>
                                    <th>Pendiente</th>
                                    <th>Estado</th>
                                    <th>Condicion</th>
                                    <th class="text-end">Devolucion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entrega->detalles as $detalle)
                                    <tr>
                                        <td>
                                            {{ $detalle->articulo?->nombre ?? 'N/A' }}
                                            <small class="d-block text-muted">{{ $detalle->articulo?->codigo_producto ?? '-' }}</small>
                                        </td>
                                        <td>{{ $detalle->cantidad_entregada }}</td>
                                        <td>{{ $detalle->cantidad_devuelta }}</td>
                                        <td>{{ $detalle->cantidadPendiente() }}</td>
                                        <td>{{ $estadoLabels[$detalle->estado] ?? ucfirst((string) $detalle->estado) }}</td>
                                        <td>
                                            {{ $detalle->condicion_entrega ?: '-' }}
                                            @if ($detalle->condicion_devolucion)
                                                <small class="d-block text-muted">Dev.: {{ $detalle->condicion_devolucion }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($detalle->cantidadPendiente() > 0)
                                                <form method="POST" action="{{ route('admin.entregas-herramientas.devolver', [$entrega->id, $detalle->id]) }}" class="row g-2 justify-content-end">
                                                    @csrf
                                                    <div class="col-12 col-lg-2">
                                                        <input type="number" name="cantidad_devuelta" class="form-control form-control-sm" min="1" max="{{ $detalle->cantidadPendiente() }}" value="{{ $detalle->cantidadPendiente() }}" required>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <input type="date" name="fecha_devolucion" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <select name="estado" class="form-select form-select-sm" required>
                                                            <option value="devuelta">Devuelta</option>
                                                            <option value="rota">Rota</option>
                                                            <option value="perdida">Perdida</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <input type="text" name="condicion_devolucion" class="form-control form-control-sm" placeholder="Condicion">
                                                    </div>
                                                    <div class="col-12 col-lg-2">
                                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                                            <i class="bi bi-arrow-return-left"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <span class="text-muted">Completo</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('admin.partials.documentos-operativos', [
                'documentos' => $documentos,
                'documentableType' => 'entrega_herramienta',
                'documentableId' => $entrega->id,
                'editPermission' => 'entregas-herramientas.editar',
            ])
        </section>
    </div>

    @include('admin.entregas-herramientas.partials.print-planilla', ['entrega' => $entrega])
@endsection

@include('admin.entregas-herramientas.partials.print-script')
