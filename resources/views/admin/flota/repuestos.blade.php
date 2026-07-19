@extends('layouts.admin')

@php
    $estadoBadges = [
        'activo' => 'bg-light-success',
        'inactivo' => 'bg-light-secondary',
    ];
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Repuestos del vehiculo {{ $flota->nro_interno }}</h3>
                <p class="text-subtitle text-muted">
                    {{ $flota->dominio }} - {{ trim(($flota->marcaVehiculo?->nombre ?? '') . ' ' . ($flota->tipoVehiculo?->nombre ?? '')) ?: 'Vehiculo' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                @can('flota-repuestos.crear')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRepuestoModal">
                        <i class="bi bi-plus-circle"></i> Agregar repuesto
                    </button>
                @endcan
                <a href="{{ route('admin.flota.index') }}" class="btn btn-light-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ficha tecnica de repuestos</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    <th>Repuesto</th>
                                    <th>Codigo / referencia</th>
                                    <th>Kit de servicio</th>
                                    <th>Marca</th>
                                    <th>Observaciones</th>
                                    <th>Estado</th>
                                    @if (auth()->user()?->can('flota-repuestos.editar') || auth()->user()?->can('flota-repuestos.eliminar'))
                                        <th class="text-end" style="width: 160px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flota->repuestos->sortBy(fn ($repuesto) => $repuesto->articulo?->nombre ?? $repuesto->nombre_repuesto) as $repuesto)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $repuesto->articulo?->nombre ?? $repuesto->nombre_repuesto ?? 'N/A' }}
                                            </div>
                                            @if ($repuesto->articulo)
                                                <small class="text-muted">
                                                    Articulo {{ $repuesto->articulo->codigo_producto ?? 'sin codigo' }}
                                                    {{ $repuesto->articulo->unidadMedida ? '- ' . $repuesto->articulo->unidadMedida->nombre : '' }}
                                                </small>
                                            @else
                                                <small class="text-muted">Carga manual</small>
                                            @endif
                                        </td>
                                        <td>{{ $repuesto->codigo_referencia ?: '-' }}</td>
                                        <td>
                                            @if ($repuesto->configuracionIntervaloServicio)
                                                <div class="fw-semibold">
                                                    {{ $repuesto->configuracionIntervaloServicio->etiqueta() }}
                                                </div>
                                                <small class="text-muted">
                                                    Cant. {{ $repuesto->cantidad_servicio ?? 1 }} -
                                                    {{ ($repuesto->modo_carga_servicio ?? 'manual') === 'automatico' ? 'Automatica' : 'Manual' }}
                                                    @if ($repuesto->obligatorio_servicio)
                                                        - Obligatorio
                                                    @endif
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $repuesto->marca ?: '-' }}</td>
                                        <td>{{ $repuesto->observaciones ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $estadoBadges[$repuesto->estado] ?? 'bg-light-secondary' }}">
                                                {{ ucfirst($repuesto->estado) }}
                                            </span>
                                        </td>
                                        @if (auth()->user()?->can('flota-repuestos.editar') || auth()->user()?->can('flota-repuestos.eliminar'))
                                            <td class="text-end">
                                                @can('flota-repuestos.editar')
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#editRepuestoModal-{{ $repuesto->id }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                @endcan
                                                @can('flota-repuestos.eliminar')
                                                    <form method="POST" action="{{ route('admin.flota.repuestos.destroy', [$flota->id, $repuesto->id]) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirmFormSubmit(this, 'Esta seguro de eliminar este repuesto?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()?->can('flota-repuestos.editar') || auth()->user()?->can('flota-repuestos.eliminar') ? 8 : 7 }}" class="text-center text-muted py-4">
                                            No hay repuestos configurados para este vehiculo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @can('flota-repuestos.crear')
        <div class="modal fade" id="createRepuestoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.flota.repuestos.store', $flota->id) }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" style="color: white">Agregar repuesto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.flota.partials.repuesto-form', ['repuesto' => null, 'modalId' => 'createRepuestoModal'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @can('flota-repuestos.editar')
        @foreach ($flota->repuestos as $repuesto)
            <div class="modal fade" id="editRepuestoModal-{{ $repuesto->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('admin.flota.repuestos.update', [$flota->id, $repuesto->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" style="color: white">Editar repuesto</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('admin.flota.partials.repuesto-form', [
                                'repuesto' => $repuesto,
                                'modalId' => 'editRepuestoModal-' . $repuesto->id,
                            ])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
@endsection

@push('scripts')
    <script data-open-modal="{{ session('open_modal') }}">
        (function() {
            const openModalId = document.currentScript.dataset.openModal;

            if (!openModalId || typeof bootstrap === 'undefined') {
                return;
            }

            const modalElement = document.getElementById(openModalId);

            if (!modalElement) {
                return;
            }

            new bootstrap.Modal(modalElement).show();
        })();
    </script>
@endpush
