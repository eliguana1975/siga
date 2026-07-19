@extends('layouts.admin')

@php
    $estadoLabels = [
        'entregada' => 'Entregada',
        'parcial' => 'Parcial',
        'devuelta' => 'Devuelta',
        'cancelada' => 'Cancelada',
    ];

    $estadoBadges = [
        'entregada' => 'bg-light-warning',
        'parcial' => 'bg-light-info',
        'devuelta' => 'bg-light-success',
        'cancelada' => 'bg-light-secondary',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Entrega de herramientas</h3>
                <p class="text-subtitle text-muted">Controla las herramientas asignadas a empleados y sus devoluciones.</p>
            </div>
            @can('entregas-herramientas.crear')
                <a href="{{ route('admin.entregas-herramientas.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva entrega
                </a>
            @endcan
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Entregas registradas</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.entregas-herramientas.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar entrega</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Empleado, herramienta, deposito o estado">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.entregas-herramientas.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Empleado</th>
                                    <th>Deposito</th>
                                    <th>Fecha</th>
                                    <th>Herramientas</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entregas as $entrega)
                                    <tr>
                                        <td>{{ $entregas->firstItem() + $loop->index }}</td>
                                        <td>{{ trim(($entrega->empleado?->apellidos ?? '') . ' ' . ($entrega->empleado?->nombres ?? '')) ?: 'N/A' }}</td>
                                        <td>{{ $entrega->deposito?->nombre ?? '-' }}</td>
                                        <td>{{ $entrega->fecha_entrega?->format('d/m/Y') }}</td>
                                        <td>
                                            {{ (int) ($entrega->detalles_count ?? 0) }}
                                            <small class="d-block text-muted">{{ (int) ($entrega->detalles_sum_cantidad_entregada ?? 0) }} unidad(es)</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$entrega->estado] ?? 'bg-light-secondary' }}">
                                                {{ $estadoLabels[$entrega->estado] ?? ucfirst((string) $entrega->estado) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.entregas-herramientas.show', $entrega->id) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('entregas-herramientas.editar')
                                                <a href="{{ route('admin.entregas-herramientas.edit', $entrega->id) }}" class="btn btn-sm btn-success" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endcan
                                            <button type="button" class="btn btn-sm btn-secondary" title="Planilla"
                                                onclick="printEntregaHerramienta('printEntregaHerramienta-{{ $entrega->id }}')">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No hay entregas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($entregas->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $entregas->firstItem() }} a {{ $entregas->lastItem() }} de {{ $entregas->total() }} registros
                            </small>
                            <div>{{ $entregas->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($entregas as $entrega)
        @include('admin.entregas-herramientas.partials.print-planilla', ['entrega' => $entrega])
    @endforeach
@endsection

@include('admin.entregas-herramientas.partials.print-script')
