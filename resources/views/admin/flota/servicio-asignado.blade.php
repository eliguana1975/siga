@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h3>Asignar servicio</h3>
                <p class="text-subtitle text-muted">
                    Vehiculo {{ $flota->nro_interno }} - {{ $flota->dominio }}
                </p>
            </div>
            <a href="{{ route('admin.flota.index') }}" class="btn btn-light-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="row">
                <div class="col-12 col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Servicio actual</h4>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.flota.servicio-asignado.update', $flota->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="servicio_asignado_actual_id" class="form-label">Servicio asignado</label>
                                    <select name="servicio_asignado_actual_id" id="servicio_asignado_actual_id" class="form-select">
                                        <option value="">Sin servicio asignado</option>
                                        @foreach ($serviciosAsignados as $servicioAsignado)
                                            <option value="{{ $servicioAsignado->id }}" @selected(old('servicio_asignado_actual_id', $flota->servicio_asignado_actual_id) == $servicioAsignado->id)>
                                                {{ $servicioAsignado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones del cambio</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.flota.index') }}" class="btn btn-light-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Actualizar servicio
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Historial de servicios</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Servicio</th>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($flota->historialServiciosAsignados->sortByDesc('fecha_desde') as $historial)
                                            <tr>
                                                <td>{{ $historial->servicioAsignado?->nombre }}</td>
                                                <td>{{ optional($historial->fecha_desde)->format('d/m/Y') }}</td>
                                                <td>{{ optional($historial->fecha_hasta)->format('d/m/Y') ?? 'Actual' }}</td>
                                                <td>{{ $historial->observaciones ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    Este vehiculo todavia no tiene historial de servicios asignados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
