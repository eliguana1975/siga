@extends('layouts.admin')

@php
    $estadoLabels = [
        'pendiente' => 'Pendiente',
        'enviada' => 'Enviada',
        'parcial' => 'Devuelta parcial',
        'completada' => 'Completada',
        'vencida' => 'Vencida',
        'cancelada' => 'Cancelada',
        'devuelta_parcial' => 'Devuelta parcial',
        'devuelta_total' => 'Devuelta total',
    ];

    $pendientes = (int) $reparacion->detalles->sum(fn ($detalle) => $detalle->cantidadPendiente());
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Reparacion {{ $reparacion->numero_orden }}</h3>
                <p class="text-subtitle text-muted">Seguimiento de devoluciones, pendientes y reclamos al proveedor.</p>
            </div>
            <div class="d-flex gap-2">
                @can('reparaciones-articulos.editar')
                    <a href="{{ route('admin.reparaciones-articulos.edit', $reparacion->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form method="POST" action="{{ route('admin.reparaciones-articulos.destroy', $reparacion->id) }}" onsubmit="return confirm('¿Seguro que desea eliminar esta orden de reparacion?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                @endcan
                @can('reparaciones-articulos.imprimir')
                    <button type="button" class="btn btn-secondary" onclick="printReparacionPlanilla('printReparacionPlanilla-{{ $reparacion->id }}')">
                        <i class="bi bi-printer"></i> Planilla envio
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="printReparacionRotulos('printReparacionRotulos-{{ $reparacion->id }}')">
                        <i class="bi bi-tag"></i> Rotulos envio
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="printReparacionReclamo('printReparacionReclamo-{{ $reparacion->id }}')">
                        <i class="bi bi-printer"></i> Planilla reclamo
                    </button>
                @endcan
                <a href="{{ route('admin.reparaciones-articulos.index') }}" class="btn btn-light-secondary">
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
                            <small class="text-muted d-block">Numero orden</small>
                            <strong>{{ $reparacion->numero_orden }}</strong>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Estado</small>
                            <span class="badge bg-light-primary">{{ $estadoLabels[$reparacion->estado] ?? ucfirst((string) $reparacion->estado) }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Fecha envio</small>
                            <strong>{{ $reparacion->fecha_envio?->format('d/m/Y') }}</strong>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Fecha compromiso</small>
                            <strong>{{ $reparacion->fecha_compromiso?->format('d/m/Y') ?? '-' }}</strong>
                        </div>
                        <div class="col-12 col-md-4">
                            <small class="text-muted d-block">Proveedor</small>
                            <strong>{{ $reparacion->proveedor?->nombre ?? '-' }}</strong>
                        </div>
                        <div class="col-12 col-md-4">
                            <small class="text-muted d-block">Provincia / Ciudad</small>
                            <strong>{{ $reparacion->provincia?->nombre ?? '-' }} / {{ $reparacion->ciudad?->nombre ?? '-' }}</strong>
                        </div>
                        <div class="col-12 col-md-4">
                            <small class="text-muted d-block">Telefono</small>
                            <strong>{{ $reparacion->telefono ?: '-' }}</strong>
                        </div>
                        <div class="col-12 col-md-8">
                            <small class="text-muted d-block">Domicilio</small>
                            <span>{{ $reparacion->domicilio ?: '-' }}</span>
                        </div>
                        <div class="col-12 col-md-4">
                            <small class="text-muted d-block">Codigo postal</small>
                            <span>{{ $reparacion->codigo_postal ?: '-' }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Quien envia</small>
                            <span>{{ $reparacion->quien_envia_nombre ?: '-' }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Doc. envia</small>
                            <span>{{ $reparacion->quien_envia_documento ?: '-' }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Quien recibe</small>
                            <span>{{ $reparacion->quien_recibe_nombre ?: '-' }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <small class="text-muted d-block">Doc. recibe</small>
                            <span>{{ $reparacion->quien_recibe_documento ?: '-' }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Observaciones</small>
                            <span>{{ $reparacion->observaciones ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.partials.documentos-operativos', [
                'documentos' => $documentos ?? collect(),
                'documentableType' => 'reparacion_articulo',
                'documentableId' => $reparacion->id,
                'editPermission' => 'reparaciones-articulos.editar',
            ])

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Articulos enviados</h4>
                    <span class="badge {{ $pendientes > 0 ? 'bg-light-danger' : 'bg-light-success' }}">
                        Pendiente total: {{ $pendientes }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Articulo</th>
                                    <th>Enviada</th>
                                    <th>Devuelta</th>
                                    <th>Pendiente</th>
                                    <th>Estado</th>
                                    <th>Costo unit.</th>
                                    <th class="text-end">Registrar devolucion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reparacion->detalles as $detalle)
                                    @php
                                        $pendiente = $detalle->cantidadPendiente();
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $detalle->nombreArticulo() }}
                                            <small class="d-block text-muted">{{ $detalle->codigoArticulo() }}</small>
                                        </td>
                                        <td>{{ $detalle->cantidad_enviada }}</td>
                                        <td>{{ $detalle->cantidad_devuelta }}</td>
                                        <td>
                                            <span class="fw-bold {{ $pendiente > 0 ? 'text-danger' : 'text-success' }}">{{ $pendiente }}</span>
                                        </td>
                                        <td>{{ $estadoLabels[$detalle->estado] ?? ucfirst((string) $detalle->estado) }}</td>
                                        <td>{{ $detalle->costo_unitario !== null ? number_format((float) $detalle->costo_unitario, 2, ',', '.') : '-' }}</td>
                                        <td class="text-end">
                                            @if ($pendiente > 0)
                                                @can('reparaciones-articulos.editar')
                                                    <form method="POST" action="{{ route('admin.reparaciones-articulos.devolver', [$reparacion->id, $detalle->id]) }}" class="row g-2 justify-content-end">
                                                        @csrf
                                                        <div class="col-12 col-lg-2">
                                                            <input type="number" name="cantidad_devuelta" class="form-control form-control-sm" min="1" max="{{ $pendiente }}" value="{{ $pendiente }}" required>
                                                        </div>
                                                        <div class="col-12 col-lg-3">
                                                            <input type="date" name="fecha_devolucion" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                                                        </div>
                                                        <div class="col-12 col-lg-3">
                                                            <input type="number" name="costo_unitario" step="0.01" min="0" class="form-control form-control-sm" placeholder="Costo" value="{{ $detalle->costo_unitario }}">
                                                        </div>
                                                        <div class="col-12 col-lg-3">
                                                            <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Observaciones">
                                                        </div>
                                                        <div class="col-12 col-lg-1">
                                                            <button type="submit" class="btn btn-sm btn-success w-100" title="Registrar devolucion">
                                                                <i class="bi bi-check2-circle"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                @endcan
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

            @can('reparaciones-articulos.reclamar')
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Registrar reclamo al proveedor</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.reparaciones-articulos.reclamos.store', $reparacion->id) }}" class="row g-3">
                            @csrf
                            <div class="col-12 col-md-4">
                                <label class="form-label">Detalle reclamado (opcional)</label>
                                <select name="reparacion_articulo_detalle_id" class="form-select">
                                    <option value="">Todos los pendientes</option>
                                    @foreach ($reparacion->detalles as $detalle)
                                        @if ($detalle->cantidadPendiente() > 0)
                                            <option value="{{ $detalle->id }}">
                                                {{ $detalle->nombreArticulo() }} - Pendiente {{ $detalle->cantidadPendiente() }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Fecha reclamo (*)</label>
                                <input type="date" name="fecha_reclamo" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Medio (*)</label>
                                <select name="medio" class="form-select" required>
                                    <option value="telefono">Telefono</option>
                                    <option value="email">Email</option>
                                    <option value="whatsapp">Whatsapp</option>
                                    <option value="presencial">Presencial</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Numero referencia</label>
                                <input type="text" name="numero_referencia" class="form-control" maxlength="120" placeholder="Ticket / mail / llamada">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2" placeholder="Detalle del reclamo"></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Respuesta proveedor</label>
                                <textarea name="respuesta_proveedor" class="form-control" rows="2" placeholder="Respuesta recibida"></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-megaphone"></i> Guardar reclamo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Historial de reclamos</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Medio</th>
                                    <th>Detalle</th>
                                    <th>Referencia</th>
                                    <th>Observaciones</th>
                                    <th>Respuesta</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reparacion->reclamos as $reclamo)
                                    <tr>
                                        <td>{{ $reclamo->fecha_reclamo?->format('d/m/Y') }}</td>
                                        <td>{{ ucfirst((string) $reclamo->medio) }}</td>
                                        <td>{{ $reclamo->detalle?->nombreArticulo() ?? 'General' }}</td>
                                        <td>{{ $reclamo->numero_referencia ?: '-' }}</td>
                                        <td>{{ $reclamo->observaciones ?: '-' }}</td>
                                        <td>{{ $reclamo->respuesta_proveedor ?: '-' }}</td>
                                        <td>{{ $reclamo->usuario?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No hay reclamos cargados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('admin.reparaciones-articulos.partials.print-planilla', ['reparacion' => $reparacion])
    @include('admin.reparaciones-articulos.partials.print-rotulos', ['reparacion' => $reparacion])
    @include('admin.reparaciones-articulos.partials.print-reclamo', ['reparacion' => $reparacion])
@endsection

@include('admin.reparaciones-articulos.partials.print-script')
