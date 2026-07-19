@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Solicitud de repuesto #{{ $solicitud->id }}</h3>
                <p class="text-subtitle text-muted">Seguimiento desde taller hasta pedido, ingreso y colocacion.</p>
            </div>
            <div class="d-flex gap-2">
                @can('solicitudes-repuestos.editar')
                    <a href="{{ route('admin.solicitudes-repuestos.edit', $solicitud) }}" class="btn btn-success">
                        <i class="bi bi-pencil-square"></i> Editar
                    </a>
                @endcan
                <a href="{{ route('admin.solicitudes-repuestos.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $type)
                @if (session($key))
                    <div class="alert alert-{{ $type }} alert-dismissible show fade">
                        {{ session($key) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @endforeach

            <div class="row g-3">
                <div class="col-12 col-xl-7">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Datos de la solicitud</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <small class="text-muted fw-semibold">Repuesto</small>
                                    <div class="fw-semibold">{{ $solicitud->descripcion_repuesto }}</div>
                                    <small class="text-muted">{{ $solicitud->codigo_repuesto ?: 'Sin codigo' }}</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted fw-semibold">Cantidad</small>
                                    <div class="fw-semibold">{{ $solicitud->cantidad }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted fw-semibold">Prioridad</small>
                                    <div><span class="badge {{ $solicitud->prioridadBadge() }}">{{ $solicitud->prioridadLabel() }}</span></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted fw-semibold">Estado</small>
                                    <div><span class="badge {{ $solicitud->estadoBadge() }}">{{ $solicitud->estadoLabel() }}</span></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted fw-semibold">Fecha</small>
                                    <div class="fw-semibold">{{ $solicitud->fecha_solicitud?->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <small class="text-muted fw-semibold">Solicitante</small>
                                    <div class="fw-semibold">{{ $solicitud->solicitante?->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <small class="text-muted fw-semibold">Vehiculo</small>
                                    <div class="fw-semibold">
                                        {{ $solicitud->flota ? $solicitud->flota->nro_interno . ' - ' . $solicitud->flota->dominio : 'Sin vehiculo' }}
                                    </div>
                                    <small class="text-muted">Chasis: {{ $solicitud->nro_chasis ?: ($solicitud->flota?->nro_chasis ?: '-') }}</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <small class="text-muted fw-semibold">Orden de trabajo</small>
                                    <div class="fw-semibold">
                                        @if ($solicitud->ordenTrabajo)
                                            OT #{{ $solicitud->ordenTrabajo->id }} - {{ $solicitud->ordenTrabajo->titulo }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted fw-semibold">Motivo</small>
                                    <div>{{ $solicitud->motivo ?: '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted fw-semibold">Observaciones taller</small>
                                    <div>{{ $solicitud->observaciones_taller ?: '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted fw-semibold">Observaciones compras</small>
                                    <div>{{ $solicitud->observaciones_compras ?: '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted fw-semibold">Proveedor compras</small>
                                    <div>{{ $solicitud->proveedor_sugerido ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Adjuntos y vinculos</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach (['foto_repuesto_path' => 'Foto repuesto', 'foto_contexto_path' => 'Foto referencia'] as $field => $label)
                                    <div class="col-12 col-md-6">
                                        <small class="text-muted fw-semibold">{{ $label }}</small>
                                        @if ($solicitud->{$field})
                                            <a href="{{ asset('storage/' . $solicitud->{$field}) }}" target="_blank" class="d-block mt-1">
                                                <img src="{{ asset('storage/' . $solicitud->{$field}) }}" alt="{{ $label }}" class="img-fluid rounded border" style="max-height: 180px; object-fit: cover;">
                                            </a>
                                        @else
                                            <div class="text-muted">Sin imagen</div>
                                        @endif
                                    </div>
                                @endforeach
                                <div class="col-12">
                                    <small class="text-muted fw-semibold">Articulo catalogado</small>
                                    <div class="fw-semibold">
                                        @if ($solicitud->articulo)
                                            {{ $solicitud->articulo->nombre }}
                                            <small class="text-muted">({{ $solicitud->articulo->codigo_producto ?: 'Sin codigo' }})</small>
                                        @else
                                            Sin articulo asociado
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted fw-semibold">Pedido generado</small>
                                    <div class="fw-semibold">
                                        @if ($solicitud->pedidoArticulo)
                                            <a href="{{ route('admin.pedidos-articulos.show', $solicitud->pedidoArticulo->id) }}">
                                                Pedido #{{ $solicitud->pedidoArticulo->id }}
                                            </a>
                                            <span class="text-muted">({{ ucfirst((string) $solicitud->pedidoArticulo->estado) }})</span>
                                        @else
                                            Sin pedido generado
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                @canany(['solicitudes-repuestos.aprobar', 'solicitudes-repuestos.rechazar'])
                    <div class="col-12 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header"><h4 class="card-title mb-0">Revision compras</h4></div>
                            <div class="card-body">
                                @can('solicitudes-repuestos.aprobar')
                                    <form method="POST" action="{{ route('admin.solicitudes-repuestos.aprobar', $solicitud) }}" class="mb-3">
                                        @csrf
                                        <label class="form-label">Proveedor compras</label>
                                        <input type="text" name="proveedor_sugerido" class="form-control mb-2"
                                            value="{{ old('proveedor_sugerido', $solicitud->proveedor_sugerido) }}"
                                            maxlength="160">
                                        <label class="form-label">Observacion</label>
                                        <textarea name="observaciones_compras" class="form-control mb-2" rows="2">{{ old('observaciones_compras') }}</textarea>
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-check-circle"></i> Aprobar
                                        </button>
                                    </form>
                                @endcan
                                @can('solicitudes-repuestos.rechazar')
                                    <form method="POST" action="{{ route('admin.solicitudes-repuestos.rechazar', $solicitud) }}">
                                        @csrf
                                        <label class="form-label">Motivo rechazo (*)</label>
                                        <textarea name="observaciones_compras" class="form-control mb-2" rows="2" required></textarea>
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="bi bi-x-circle"></i> Rechazar
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endcanany

                @can('solicitudes-repuestos.catalogar')
                    <div class="col-12 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header"><h4 class="card-title mb-0">Catalogar repuesto</h4></div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.solicitudes-repuestos.asociar-articulo', $solicitud) }}" class="mb-3">
                                    @csrf
                                    <label class="form-label">Asociar articulo existente</label>
                                    <select name="articulo_id" class="form-select js-select2 mb-2" data-placeholder="Seleccione articulo" required>
                                        <option value="">Seleccione articulo</option>
                                        @foreach ($articulos as $articulo)
                                            <option value="{{ $articulo->id }}">{{ $articulo->nombre }} - {{ $articulo->codigo_producto ?: 'Sin codigo' }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-link-45deg"></i> Asociar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.solicitudes-repuestos.crear-articulo', $solicitud) }}">
                                    @csrf
                                    <label class="form-label">Crear articulo</label>
                                    <input type="text" name="nombre" class="form-control mb-2" value="{{ old('nombre', $solicitud->descripcion_repuesto) }}" required>
                                    <input type="text" name="codigo_producto" class="form-control mb-2" value="{{ old('codigo_producto', $solicitud->codigo_repuesto) }}" placeholder="Codigo">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <select name="categoria_id" class="form-select mb-2" required>
                                                <option value="">Categoria</option>
                                                @foreach ($categorias as $categoria)
                                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <select name="unidad_medida_id" class="form-select mb-2" required>
                                                <option value="">Unidad</option>
                                                @foreach ($unidadesMedida as $unidad)
                                                    <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4"><input type="number" name="stock_minimo" class="form-control mb-2" min="0" value="0" placeholder="Min"></div>
                                        <div class="col-4"><input type="number" name="stock_maximo" class="form-control mb-2" min="0" value="0" placeholder="Max"></div>
                                        <div class="col-4"><input type="number" name="stock_pedido" class="form-control mb-2" min="0" value="0" placeholder="Pedido"></div>
                                    </div>
                                    <textarea name="observaciones" class="form-control mb-2" rows="2" placeholder="Observaciones"></textarea>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle"></i> Crear y asociar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('solicitudes-repuestos.generar-pedido')
                    <div class="col-12 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header"><h4 class="card-title mb-0">Pedido y seguimiento</h4></div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.solicitudes-repuestos.generar-pedido', $solicitud) }}" class="mb-3">
                                    @csrf
                                    <label class="form-label">Deposito destino (*)</label>
                                    <select name="deposito_id" class="form-select mb-2" required @disabled(! $solicitud->articulo_id || $solicitud->pedido_articulo_id)>
                                        <option value="">Seleccione deposito</option>
                                        @foreach ($depositos as $deposito)
                                            <option value="{{ $deposito->id }}" @selected($solicitud->deposito_id === $deposito->id)>{{ $deposito->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label">Cantidad (*)</label>
                                    <input type="number" name="cantidad" class="form-control mb-2" value="{{ $solicitud->cantidad }}" min="1" required @disabled(! $solicitud->articulo_id || $solicitud->pedido_articulo_id)>
                                    <textarea name="notas" class="form-control mb-2" rows="2" placeholder="Notas para el pedido" @disabled(! $solicitud->articulo_id || $solicitud->pedido_articulo_id)></textarea>
                                    <button type="submit" class="btn btn-primary w-100" @disabled(! $solicitud->articulo_id || $solicitud->pedido_articulo_id)>
                                        <i class="bi bi-cart-plus"></i> Generar pedido
                                    </button>
                                </form>
                                @can('solicitudes-repuestos.cerrar')
                                    <form method="POST" action="{{ route('admin.solicitudes-repuestos.estado', $solicitud) }}">
                                        @csrf
                                        <label class="form-label">Actualizar estado</label>
                                        <select name="estado" class="form-select mb-2" required>
                                            @foreach (['comprado', 'ingresado', 'entregado_taller', 'colocado', 'cerrado'] as $estado)
                                                <option value="{{ $estado }}">{{ \App\Models\SolicitudRepuesto::ESTADOS[$estado] }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="observaciones_compras" class="form-control mb-2" rows="2" placeholder="Observacion"></textarea>
                                        <button type="submit" class="btn btn-secondary w-100">
                                            <i class="bi bi-arrow-repeat"></i> Actualizar
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endcan
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12">
                    @include('admin.partials.documentos-operativos', [
                        'documentos' => $documentos ?? collect(),
                        'documentableType' => 'solicitud_repuesto',
                        'documentableId' => $solicitud->id,
                        'editPermission' => 'solicitudes-repuestos.editar',
                    ])
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Historial de aprobaciones y cambios</h4>
                        </div>
                        <div class="card-body">
                            @if (($historial ?? collect())->isEmpty())
                                <div class="text-muted">Sin movimientos sensibles registrados para esta solicitud.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Accion</th>
                                                <th>Estado</th>
                                                <th>Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($historial as $evento)
                                                <tr>
                                                    <td>{{ $evento->created_at?->format('d/m/Y H:i') }}</td>
                                                    <td>{{ $evento->usuario?->name ?: $evento->usuario_nombre ?: 'Sistema' }}</td>
                                                    <td>{{ str_replace(['solicitudes_repuestos.', '_'], ['', ' '], $evento->accion) }}</td>
                                                    <td>
                                                        @php
                                                            $estadoAnterior = $evento->metadata['estado_anterior'] ?? null;
                                                            $estadoNuevo = $evento->metadata['estado_nuevo'] ?? null;
                                                        @endphp
                                                        @if ($estadoAnterior || $estadoNuevo)
                                                            <span class="text-muted">{{ $estadoAnterior ?: '-' }}</span>
                                                            <i class="bi bi-arrow-right mx-1"></i>
                                                            <span class="fw-semibold">{{ $estadoNuevo ?: '-' }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $evento->descripcion }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
