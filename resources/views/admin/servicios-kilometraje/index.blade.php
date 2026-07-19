@extends('layouts.admin')

@php
    $estadoBadge = [
        'vencido' => 'bg-light-danger',
        'proximo' => 'bg-light-warning',
        'ok' => 'bg-light-success',
    ];

    $estadoLabel = [
        'vencido' => 'Realizar',
        'proximo' => 'Proximo',
        'ok' => 'Al dia',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Servicios por medidor</h3>
                <p class="text-subtitle text-muted">
                    Controla kilometros u horas de trabajo de cada unidad y cuanto falta para cada tipo de servicio.
                </p>
            </div>
            <a href="{{ route('admin.configuracion-intervalos-servicio.index') }}" class="btn btn-primary">
                <i class="bi bi-sliders"></i> Configurar intervalos
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Estado de servicios de la flota</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.servicios-kilometraje.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="search" class="form-label mb-1">Buscar vehiculo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Interno, dominio o estado">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="medidor" class="form-label mb-1">Medidor</label>
                                <select name="medidor" id="medidor" class="form-select">
                                    <option value="todos" @selected(($medidor ?? 'todos') === 'todos')>Todos</option>
                                    <option value="km" @selected(($medidor ?? 'todos') === 'km')>Kilometros</option>
                                    <option value="horas" @selected(($medidor ?? 'todos') === 'horas')>Horas</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto">Buscar</button>
                                <a href="{{ route('admin.servicios-kilometraje.index') }}" class="btn btn-light-secondary w-100 w-md-auto">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @if ($intervalos->isEmpty())
                        <div class="alert alert-warning">
                            No hay intervalos activos configurados.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Interno</th>
                                    <th style="width: 120px;">Dominio</th>
                                    <th>Vehiculo</th>
                                    <th style="width: 160px;">Medidor actual</th>
                                    <th>Servicios</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flotas as $flota)
                                    <tr>
                                        <td class="fw-semibold">{{ $flota->nro_interno }}</td>
                                        <td>{{ $flota->dominio }}</td>
                                        <td>
                                            <div>{{ $flota->marcaVehiculo?->nombre ?? 'Sin marca' }}</div>
                                            <small class="text-muted">
                                                {{ $flota->tipoVehiculo?->nombre ?? 'Sin tipo' }}
                                                @if ($flota->tipoCaja?->nombre)
                                                    · {{ $flota->tipoCaja->nombre }}
                                                @endif
                                            </small>
                                        </td>
                                        <td class="fw-semibold">
                                            {{ number_format((int) $flota->lectura_actual_calculada, 0, ',', '.') }}
                                            {{ ($flota->tipo_medidor_calculado ?? 'km') === 'horas' ? 'hs' : 'km' }}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($flota->servicios_kilometraje as $servicio)
                                                    @php($unidadLabel = ($servicio['unidad'] ?? 'km') === 'horas' ? 'hs' : 'km')
                                                    <div class="border rounded p-2" style="min-width: 220px;">
                                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                                            <div>
                                                                <div class="fw-semibold">{{ $servicio['nombre'] }}</div>
                                                                <small class="text-muted">
                                                                    {{ $servicio['sistema_label'] ?? $servicio['sistema'] }} - ID {{ $servicio['id'] }}
                                                                </small>
                                                            </div>
                                                            <span class="badge {{ $estadoBadge[$servicio['estado']] ?? 'bg-light-secondary' }}">
                                                                {{ $estadoLabel[$servicio['estado']] ?? ucfirst($servicio['estado']) }}
                                                            </span>
                                                        </div>
                                                        <div class="mt-2 small">
                                                            <div>
                                                                Intervalo:
                                                                <span class="badge {{ $unidadLabel === 'hs' ? 'bg-light-warning' : 'bg-light-info' }}">
                                                                    Cada {{ number_format($servicio['intervalo'], 0, ',', '.') }} {{ $unidadLabel }}
                                                                </span>
                                                            </div>
                                                            <div>Proximo: {{ number_format($servicio['proximo'], 0, ',', '.') }} {{ $unidadLabel }}</div>
                                                            <div class="fw-semibold">
                                                                Faltan: {{ number_format($servicio['faltan'], 0, ',', '.') }} {{ $unidadLabel }}
                                                            </div>
                                                            @if ($servicio['ultimo_servicio_valor'])
                                                                <div class="text-muted">
                                                                    Ultimo: {{ number_format((int) $servicio['ultimo_servicio_valor'], 0, ',', '.') }} {{ $unidadLabel }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <form method="POST" action="{{ route('admin.servicios-kilometraje.registrar') }}" class="mt-2"
                                                            onsubmit="return confirmFormSubmit(this, 'Registrar este servicio como realizado para el interno {{ addslashes($flota->nro_interno) }}?');">
                                                            @csrf
                                                            <input type="hidden" name="flota_id" value="{{ $flota->id }}">
                                                            <input type="hidden" name="configuracion_intervalo_servicio_id" value="{{ $servicio['id'] }}">
                                                            <input type="hidden" name="kilometraje_servicio" value="{{ ($servicio['unidad'] ?? 'km') === 'km' ? (int) $flota->lectura_actual_calculada : 0 }}">
                                                            <input type="hidden" name="horometro_servicio" value="{{ ($servicio['unidad'] ?? 'km') === 'horas' ? (int) $flota->lectura_actual_calculada : '' }}">
                                                            <button type="submit" class="btn btn-sm btn-outline-success w-100" @disabled((int) $flota->lectura_actual_calculada <= 0)>
                                                                <i class="bi bi-check-circle"></i> Marcar realizado
                                                            </button>
                                                        </form>
                                                    </div>
                                                @empty
                                                    <span class="text-muted">Sin servicios configurados.</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
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
@endsection
