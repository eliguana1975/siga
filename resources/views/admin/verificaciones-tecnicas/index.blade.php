@extends('layouts.admin')

@php
    $estadoBadge = [
        'sin_cargar' => 'bg-light-secondary',
        'vencido' => 'bg-light-danger',
        'proximo' => 'bg-light-warning',
        'ok' => 'bg-light-success',
    ];

    $estadoLabel = [
        'sin_cargar' => 'Sin cargar',
        'vencido' => 'Vencida',
        'proximo' => 'Proxima',
        'ok' => 'Al dia',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Verificaciones tecnicas</h3>
                <p class="text-subtitle text-muted">
                    Controla los vencimientos nacionales, provinciales y CNRT de cada vehiculo.
                </p>
            </div>
            <a href="{{ route('admin.configuracion-vencimientos-verificacion.index') }}" class="btn btn-primary">
                <i class="bi bi-sliders"></i> Configurar tipos
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Estado de vencimientos de la flota</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.verificaciones-tecnicas.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar vehiculo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Interno, dominio o estado">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.verificaciones-tecnicas.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @if ($configuraciones->isEmpty())
                        <div class="alert alert-warning">
                            No hay tipos de verificaciones activos configurados.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Interno</th>
                                    <th style="width: 120px;">Dominio</th>
                                    <th>Vehiculo</th>
                                    <th>Vencimientos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flotas as $flota)
                                    <tr>
                                        <td class="fw-semibold">{{ $flota->nro_interno }}</td>
                                        <td>{{ $flota->dominio }}</td>
                                        <td>
                                            <div>{{ $flota->marcaVehiculo?->nombre ?? 'Sin marca' }}</div>
                                            <small class="text-muted">{{ $flota->tipoVehiculo?->nombre ?? 'Sin tipo' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($flota->verificaciones_tecnicas as $verificacion)
                                                    <div class="border rounded p-2" style="min-width: 260px;">
                                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                                            <div>
                                                                <div class="fw-semibold">{{ $verificacion['nombre'] }}</div>
                                                                <small class="text-muted">Alerta {{ $verificacion['dias_alerta'] }} dias antes</small>
                                                            </div>
                                                            <span class="badge {{ $estadoBadge[$verificacion['estado']] ?? 'bg-light-secondary' }}">
                                                                {{ $estadoLabel[$verificacion['estado']] ?? ucfirst($verificacion['estado']) }}
                                                            </span>
                                                        </div>
                                                        <div class="mt-2 small">
                                                            @if ($verificacion['fecha_vencimiento'])
                                                                <div>Vence: {{ $verificacion['fecha_vencimiento']->format('d/m/Y') }}</div>
                                                                @if ($verificacion['fecha_emision'])
                                                                    <div>Emision: {{ $verificacion['fecha_emision']->format('d/m/Y') }}</div>
                                                                @endif
                                                                <div class="fw-semibold">
                                                                    @if ($verificacion['dias'] < 0)
                                                                        Vencida hace {{ abs($verificacion['dias']) }} dia(s)
                                                                    @else
                                                                        Faltan {{ $verificacion['dias'] }} dia(s)
                                                                    @endif
                                                                </div>
                                                                @if ($verificacion['comprobante'])
                                                                    <div class="text-muted">Comprobante: {{ $verificacion['comprobante'] }}</div>
                                                                @endif
                                                            @else
                                                                <div class="text-muted">No hay vencimiento registrado.</div>
                                                            @endif
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-success w-100 mt-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#registrarVerificacion-{{ $flota->id }}-{{ $verificacion['id'] }}">
                                                            <i class="bi bi-calendar-check"></i> Registrar renovacion
                                                        </button>
                                                    </div>
                                                @empty
                                                    <span class="text-muted">Sin tipos de verificaciones configurados.</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No hay vehiculos para mostrar.
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
                            <div>{{ $flotas->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($flotas as $flota)
        @foreach ($flota->verificaciones_tecnicas as $verificacion)
            <div class="modal fade" id="registrarVerificacion-{{ $flota->id }}-{{ $verificacion['id'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.verificaciones-tecnicas.registrar') }}">
                            @csrf
                            <input type="hidden" name="flota_id" value="{{ $flota->id }}">
                            <input type="hidden" name="configuracion_vencimiento_verificacion_id" value="{{ $verificacion['id'] }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Registrar {{ $verificacion['nombre'] }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-light-secondary py-2">
                                    Interno {{ $flota->nro_interno }}{{ $flota->dominio ? ' - ' . $flota->dominio : '' }}
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Fecha de emision</label>
                                        <input type="date" name="fecha_emision" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Fecha de vencimiento (*)</label>
                                        <input type="date" name="fecha_vencimiento" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Comprobante</label>
                                        <input type="text" name="comprobante" class="form-control" maxlength="120">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observaciones" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Guardar vencimiento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection
