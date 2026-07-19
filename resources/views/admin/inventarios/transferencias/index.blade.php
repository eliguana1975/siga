@extends('layouts.admin')

@php
    $canCreateTransferencias = auth()->user()?->can('inventario-transferencias.crear');
    $canViewTransferencias = auth()->user()?->can('inventario-transferencias.ver');
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Transferencias entre depositos</h3>
                <p class="text-subtitle text-muted">Consulta los movimientos de articulos entre depositos.</p>
            </div>
            @if ($canCreateTransferencias)
                <a href="{{ route('admin.inventarios.transferencias.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva transferencia
                </a>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de transferencias registradas</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.inventarios.transferencias.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar transferencia</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Numero, deposito, articulo o codigo">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.inventarios.transferencias.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $transferencias->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Articulos</th>
                                    <th>Estado</th>
                                    @if ($canViewTransferencias)
                                        <th class="text-end" style="width: 130px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transferencias as $transferencia)
                                    <tr>
                                        <td>{{ $transferencias->firstItem() + $loop->index }}</td>
                                        <td>{{ $transferencia->depositoOrigen?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $transferencia->depositoDestino?->nombre ?? 'N/A' }}</td>
                                        <td>{{ $transferencia->usuario?->name ?? 'N/A' }}</td>
                                        <td>{{ $transferencia->fecha_transferencia?->format('d/m/Y') }}</td>
                                        <td>{{ (int) ($transferencia->detalles_sum_cantidad ?? 0) }} unidades en {{ (int) ($transferencia->detalles_count ?? 0) }} articulo(s)</td>
                                        <td><span class="badge bg-light-success">Confirmada</span></td>
                                        @if ($canViewTransferencias)
                                            <td class="text-end">
                                                <a href="{{ route('admin.inventarios.transferencias.show', $transferencia->id) }}" class="btn btn-sm btn-info" title="Ver">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-secondary" title="Imprimir"
                                                    onclick="printTransferenciaDeposito('printTransferenciaDeposito-{{ $transferencia->id }}')">
                                                    <i class="bi bi-printer"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canViewTransferencias ? 8 : 7 }}" class="text-center text-muted py-4">No hay transferencias registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($transferencias->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $transferencias->firstItem() }} a {{ $transferencias->lastItem() }} de {{ $transferencias->total() }} registros
                            </small>
                            <div>{{ $transferencias->links('vendor.pagination.bootstrap-5-no-summary') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @foreach ($transferencias as $transferencia)
        @include('admin.inventarios.transferencias.partials.print-planilla', ['transferencia' => $transferencia])
    @endforeach
@endsection

@include('admin.inventarios.transferencias.partials.print-script')
