@extends('layouts.admin')

@push('styles')
    <style>
        .modal .input-group .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
        }

        .modal .input-group .input-group-text i {
            line-height: 1;
        }
    </style>
@endpush

@php
    $canCreateUnidadMedidas = auth()->user()?->can('unidad-medidas.crear');
    $canEditUnidadMedidas = auth()->user()?->can('unidad-medidas.editar');
    $canDeleteUnidadMedidas = auth()->user()?->can('unidad-medidas.eliminar');
    $showUnidadMedidaActions = $canEditUnidadMedidas || $canDeleteUnidadMedidas;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestión de Unidades de Medida</h3>
                <p class="text-subtitle text-muted">
                    Administra las unidades de medida utilizadas para los artículos del inventario.
                </p>
            </div>
            @if ($canCreateUnidadMedidas)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUnidadMedidaModal">
                <i class="bi bi-rulers"></i> Nueva unidad
            </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de unidades de medida</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.unidad-medidas.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar unidad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}"
                                        placeholder="Nombre de la unidad de medida">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.unidad-medidas.index') }}"
                                    class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'unidades_medida_registradas')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'unidades_medida_registradas')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'unidades_medida_registradas')">
                                <i class="bi bi-file-earmark-pdf"></i> Crear PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="printTable('datatable')">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $unidadMedidas->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    @if ($showUnidadMedidaActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($unidadMedidas as $unidadMedida)
                                    <tr>
                                        <td>{{ $unidadMedidas->firstItem() + $loop->index }}</td>
                                        <td>{{ $unidadMedida->nombre }}</td>
                                        @if ($showUnidadMedidaActions)
                                        <td class="text-end">
                                            @if ($canEditUnidadMedidas)
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editUnidadMedidaModal-{{ $unidadMedida->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @endif

                                            @if ($canDeleteUnidadMedidas)
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteUnidadMedidaModal-{{ $unidadMedida->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showUnidadMedidaActions ? 3 : 2 }}" class="text-center text-muted py-4">No hay unidades de medida registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($unidadMedidas->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $unidadMedidas->firstItem() }} a {{ $unidadMedidas->lastItem() }} de
                                {{ $unidadMedidas->total() }} registros
                            </small>
                            <div>
                                {{ $unidadMedidas->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="createUnidadMedidaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('admin.unidad-medidas.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Crear unidad de medida</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre (*)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                value="{{ old('nombre') }}" placeholder="Nombre de la unidad" required>
                        </div>
                        @if (session('open_modal') === 'createUnidadMedidaModal')
                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($unidadMedidas as $unidadMedida)
        <div class="modal fade" id="editUnidadMedidaModal-{{ $unidadMedida->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.unidad-medidas.update', $unidadMedida->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar unidad de medida</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre-{{ $unidadMedida->id }}" class="form-label">Nombre (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                <input type="text" name="nombre" id="nombre-{{ $unidadMedida->id }}" class="form-control"
                                    value="{{ session('open_modal') === 'editUnidadMedidaModal-' . $unidadMedida->id ? old('nombre', $unidadMedida->nombre) : $unidadMedida->nombre }}"
                                    placeholder="Nombre de la unidad" required>
                            </div>
                            @if (session('open_modal') === 'editUnidadMedidaModal-' . $unidadMedida->id)
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="deleteUnidadMedidaModal-{{ $unidadMedida->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.unidad-medidas.destroy', $unidadMedida->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar unidad de medida</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">¿Está seguro de eliminar la unidad de medida <strong>{{ $unidadMedida->nombre }}</strong>?</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
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
