@extends('layouts.admin')

@section('content')
    <div class="page-heading">
        <div>
            <h3>Dashboards</h3>
            <p class="text-subtitle text-muted">
                Define que panel de inicio puede ver cada rol del sistema.
            </p>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Paneles disponibles</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible show fade">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Dashboard</th>
                                    <th>Descripcion</th>
                                    <th>Roles con acceso</th>
                                    <th>Estado</th>
                                    <th class="text-end" style="width: 130px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dashboards as $dashboard)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="{{ $dashboard->icon }}"></i>
                                                <div>
                                                    <div class="fw-semibold">{{ $dashboard->name }}</div>
                                                    <small class="text-muted">{{ $dashboard->key }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $dashboard->description ?: '-' }}</td>
                                        <td>
                                            @forelse ($dashboard->roles->sortBy('name') as $role)
                                                <span class="badge bg-light-primary me-1 mb-1">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">Sin roles asignados</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <span class="badge {{ $dashboard->is_active ? 'bg-light-success' : 'bg-light-secondary' }}">
                                                {{ $dashboard->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#editDashboardModal-{{ $dashboard->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay dashboards configurados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @foreach ($dashboards as $dashboard)
        @php
            $selectedRoles = $dashboard->roles->pluck('id')->map(fn ($id) => (int) $id)->all();
        @endphp
        <div class="modal fade" id="editDashboardModal-{{ $dashboard->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.dashboards.update', $dashboard) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar dashboard</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="dashboard-name-{{ $dashboard->id }}">Nombre (*)</label>
                                <input type="text" class="form-control" id="dashboard-name-{{ $dashboard->id }}"
                                    name="name" value="{{ old('name', $dashboard->name) }}" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="dashboard-icon-{{ $dashboard->id }}">Icono</label>
                                <input type="text" class="form-control" id="dashboard-icon-{{ $dashboard->id }}"
                                    name="icon" value="{{ old('icon', $dashboard->icon) }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="dashboard-order-{{ $dashboard->id }}">Orden</label>
                                <input type="number" min="0" max="9999" class="form-control"
                                    id="dashboard-order-{{ $dashboard->id }}" name="sort_order"
                                    value="{{ old('sort_order', $dashboard->sort_order) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="dashboard-description-{{ $dashboard->id }}">Descripcion</label>
                                <textarea class="form-control" id="dashboard-description-{{ $dashboard->id }}" name="description" rows="2">{{ old('description', $dashboard->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="dashboard-active-{{ $dashboard->id }}" name="is_active" value="1"
                                        @checked(old('is_active', $dashboard->is_active))>
                                    <label class="form-check-label" for="dashboard-active-{{ $dashboard->id }}">Activo</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Roles que pueden verlo</label>
                                <div class="row">
                                    @foreach ($roles as $role)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="roles[]"
                                                    value="{{ $role->id }}"
                                                    id="dashboard-role-{{ $dashboard->id }}-{{ $role->id }}"
                                                    @checked(in_array((int) $role->id, old('roles', $selectedRoles), true))>
                                                <label class="form-check-label" for="dashboard-role-{{ $dashboard->id }}-{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">El superusuario siempre puede ver todos los dashboards activos.</small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
