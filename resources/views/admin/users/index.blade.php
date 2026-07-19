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

        .dashboard-checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .5rem .9rem;
        }
    </style>
@endpush

@php
    $canCreateUsers = auth()->user()?->can('users.crear');
    $canEditUsers = auth()->user()?->can('users.editar');
    $canDeleteUsers = auth()->user()?->can('users.eliminar');
    $showUserActions = $canEditUsers || $canDeleteUsers;
@endphp

@section('content')
    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestión de Usuarios</h3>
                <p class="text-subtitle text-muted">
                    Administra los usuarios registrados y el rol asignado a cada uno dentro del sistema.
                </p>
            </div>
            @if ($canCreateUsers)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="bi bi-plus-circle"></i> Nuevo usuario
                </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de usuarios registrados</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}" placeholder="Nombre o correo del usuario">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('datatable', 'usuarios_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('datatable', 'usuarios_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('datatable', 'usuarios_registrados')">
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
                            Se encontraron {{ $users->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Base</th>
                                    <th>Dashboards</th>
                                    <th>Inventario</th>
                                    @if ($showUserActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    @php
                                        $protectedRoleNames = [
                                            'ADMIN',
                                            'ADMINISTRADOR',
                                            'SUPERUSUARIO',
                                            'SUPER USUARIO',
                                            'SUPERUSER',
                                            'SUPER USER',
                                        ];
                                        $protectedUserNames = [
                                            'SUPERUSUARIO',
                                            'SUPER USUARIO',
                                            'SUPERUSER',
                                            'SUPER USER',
                                        ];
                                        $userRoleNames = $user->roles->pluck('name')->map(fn ($name) => mb_strtoupper($name, 'UTF-8'));
                                        $isSuperUser = (int) $user->id === 1
                                            || in_array(mb_strtoupper($user->name, 'UTF-8'), $protectedUserNames, true)
                                            || $userRoleNames->intersect($protectedRoleNames)->isNotEmpty();
                                    @endphp
                                    <tr>
                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @forelse ($user->roles as $role)
                                                <span class="badge bg-light-secondary small me-1 mb-1">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">Sin rol</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if (($user->estado ?? 'activo') === 'activo')
                                                <span class="badge bg-light-success">Activo</span>
                                            @else
                                                <span class="badge bg-light-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $user->base?->nombre ?? 'Sin base' }}</div>
                                            @if ($user->base?->deposito)
                                                <small class="text-muted">{{ $user->base->deposito->nombre }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $assignedDashboards = $user->dashboards->sortBy('sort_order');
                                            @endphp
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse ($assignedDashboards as $dashboard)
                                                    <span class="badge {{ $user->dashboard_id === $dashboard->id ? 'bg-light-primary' : 'bg-light-secondary' }}">
                                                        {{ $dashboard->name }}
                                                    </span>
                                                @empty
                                                    @if ($user->dashboard)
                                                        <span class="badge bg-light-primary">{{ $user->dashboard->name }}</span>
                                                    @else
                                                        <span class="text-muted">Automatico por rol</span>
                                                    @endif
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            @if ($user->isSuperUsuario() || $user->puede_ver_todos_inventarios)
                                                <span class="badge bg-light-info">Todos los pañoles</span>
                                            @else
                                                <span class="badge bg-light-secondary">Base asignada</span>
                                            @endif
                                        </td>
                                        @if ($showUserActions)
                                            <td class="text-end">
                                                @if ($canEditUsers)
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal-{{ $user->id }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                @endif

                                                @if ($canDeleteUsers)
                                                    @if ($isSuperUser)
                                                        <button type="button" class="btn btn-sm btn-danger" disabled
                                                            title="No se puede eliminar el superusuario">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteUserModal-{{ $user->id }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showUserActions ? 9 : 8 }}" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($users->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }}
                                registros
                            </small>
                            <div>
                                {{ $users->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @if ($canCreateUsers)
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Crear usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                                    placeholder="Nombre del usuario" required>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="email" class="form-label">Correo (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}"
                                    placeholder="correo@ejemplo.com" required>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Mínimo 8 caracteres" required>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Repetir contraseña (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Debe coincidir con la contraseña" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="role_id" class="form-label">Rol</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                                <select name="role_id" id="role_id" class="form-select">
                                    <option value="">Sin rol</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('role_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-check-fill"></i></span>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="activo" @selected(old('estado', 'activo') === 'activo')>Activo</option>
                                    <option value="inactivo" @selected(old('estado') === 'inactivo')>Inactivo</option>
                                </select>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('estado')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="base_id" class="form-label">Base asignada</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                <select name="base_id" id="base_id" class="form-select">
                                    <option value="">Sin base</option>
                                    @foreach ($bases as $base)
                                        <option value="{{ $base->id }}" @selected((string) old('base_id') === (string) $base->id)>
                                            {{ $base->nombre }}{{ $base->deposito ? ' - ' . $base->deposito->nombre : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('base_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="dashboard_id" class="form-label">Dashboard inicial</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                                <select name="dashboard_id" id="dashboard_id" class="form-select">
                                    <option value="">Automatico</option>
                                    @foreach ($dashboards as $dashboard)
                                        <option value="{{ $dashboard->id }}" @selected((string) old('dashboard_id') === (string) $dashboard->id)>
                                            {{ $dashboard->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted">Debe estar permitido para el rol del usuario.</small>
                            @if (session('open_modal') === 'createUserModal')
                                @error('dashboard_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label d-block">Dashboards habilitados</label>
                            <div class="dashboard-checkbox-grid border rounded p-3">
                                @php
                                    $oldDashboardIds = collect(old('dashboard_ids', []))->map(fn ($id) => (string) $id)->all();
                                    $oldInitialDashboardId = (string) old('dashboard_id');
                                @endphp
                                @foreach ($dashboards as $dashboard)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dashboard_ids[]"
                                            id="dashboard_ids-{{ $dashboard->id }}" value="{{ $dashboard->id }}"
                                            @checked(in_array((string) $dashboard->id, $oldDashboardIds, true) || $oldInitialDashboardId === (string) $dashboard->id)>
                                        <label class="form-check-label" for="dashboard_ids-{{ $dashboard->id }}">
                                            <i class="{{ $dashboard->icon }}"></i> {{ $dashboard->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Estos dashboards apareceran como botones para navegar en Inicio.</small>
                            @if (session('open_modal') === 'createUserModal')
                                @error('dashboard_ids')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                                @error('dashboard_ids.*')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label d-block">Alcance de inventario</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="puede_ver_todos_inventarios" value="0">
                                <input class="form-check-input" type="checkbox" name="puede_ver_todos_inventarios"
                                    id="puede_ver_todos_inventarios" value="1" @checked(old('puede_ver_todos_inventarios'))>
                                <label class="form-check-label" for="puede_ver_todos_inventarios">
                                    Puede ver todos los pañoles
                                </label>
                            </div>
                            @if (session('open_modal') === 'createUserModal')
                                @error('puede_ver_todos_inventarios')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @foreach ($users as $user)
        @php
            $protectedRoleNames = [
                'ADMIN',
                'ADMINISTRADOR',
                'SUPERUSUARIO',
                'SUPER USUARIO',
                'SUPERUSER',
                'SUPER USER',
            ];
            $protectedUserNames = [
                'SUPERUSUARIO',
                'SUPER USUARIO',
                'SUPERUSER',
                'SUPER USER',
            ];
            $userRoleNames = $user->roles->pluck('name')->map(fn ($name) => mb_strtoupper($name, 'UTF-8'));
            $isSuperUser = (int) $user->id === 1
                || in_array(mb_strtoupper($user->name, 'UTF-8'), $protectedUserNames, true)
                || $userRoleNames->intersect($protectedRoleNames)->isNotEmpty();
            $selectedRoleId = optional($user->roles->first())->id;

            if (session('open_modal') === 'editUserModal-' . $user->id) {
                $selectedRoleId = old('role_id', $selectedRoleId);
            }
            $selectedBaseId = session('open_modal') === 'editUserModal-' . $user->id
                ? old('base_id', $user->base_id)
                : $user->base_id;
            $selectedDashboardId = session('open_modal') === 'editUserModal-' . $user->id
                ? old('dashboard_id', $user->dashboard_id)
                : $user->dashboard_id;
            $selectedDashboardIds = session('open_modal') === 'editUserModal-' . $user->id
                ? collect(old('dashboard_ids', $user->dashboards->pluck('id')->all()))->map(fn ($id) => (string) $id)->all()
                : $user->dashboards->pluck('id')->map(fn ($id) => (string) $id)->all();
            if ($selectedDashboardId) {
                $selectedDashboardIds[] = (string) $selectedDashboardId;
                $selectedDashboardIds = array_values(array_unique($selectedDashboardIds));
            }
            $selectedEstado = session('open_modal') === 'editUserModal-' . $user->id
                ? old('estado', $user->estado ?? 'activo')
                : ($user->estado ?? 'activo');
            $selectedPuedeVerTodosInventarios = session('open_modal') === 'editUserModal-' . $user->id
                ? (bool) old('puede_ver_todos_inventarios', $user->puede_ver_todos_inventarios)
                : (bool) $user->puede_ver_todos_inventarios;
        @endphp

        @if ($canEditUsers)
        <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="name-{{ $user->id }}" class="form-label">Nombre (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" name="name" id="name-{{ $user->id }}" class="form-control"
                                        value="{{ session('open_modal') === 'editUserModal-' . $user->id ? old('name', $user->name) : $user->name }}"
                                        placeholder="Nombre del usuario" required>
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="email-{{ $user->id }}" class="form-label">Correo (*)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" name="email" id="email-{{ $user->id }}" class="form-control"
                                        value="{{ session('open_modal') === 'editUserModal-' . $user->id ? old('email', $user->email) : $user->email }}"
                                        placeholder="correo@ejemplo.com" required>
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="password-{{ $user->id }}" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password" id="password-{{ $user->id }}"
                                        class="form-control" placeholder="Dejar vacío para no cambiar">
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="password_confirmation-{{ $user->id }}" class="form-label">Repetir contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation"
                                        id="password_confirmation-{{ $user->id }}" class="form-control"
                                        placeholder="Completar solo si cambias la contraseña">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="role_id-{{ $user->id }}" class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                                    <select name="role_id" id="role_id-{{ $user->id }}" class="form-select">
                                        <option value="">Sin rol</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                @selected((string) $selectedRoleId === (string) $role->id)>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('role_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="estado-{{ $user->id }}" class="form-label">Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-check-fill"></i></span>
                                    <select name="estado" id="estado-{{ $user->id }}" class="form-select" required>
                                        <option value="activo" @selected($selectedEstado === 'activo')>Activo</option>
                                        <option value="inactivo" @selected($selectedEstado === 'inactivo')>Inactivo</option>
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('estado')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="base_id-{{ $user->id }}" class="form-label">Base asignada</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <select name="base_id" id="base_id-{{ $user->id }}" class="form-select">
                                        <option value="">Sin base</option>
                                        @foreach ($bases as $base)
                                            <option value="{{ $base->id }}"
                                                @selected((string) $selectedBaseId === (string) $base->id)>
                                                {{ $base->nombre }}{{ $base->deposito ? ' - ' . $base->deposito->nombre : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('base_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="dashboard_id-{{ $user->id }}" class="form-label">Dashboard inicial</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                                    <select name="dashboard_id" id="dashboard_id-{{ $user->id }}" class="form-select">
                                        <option value="">Automatico</option>
                                        @foreach ($dashboards as $dashboard)
                                            <option value="{{ $dashboard->id }}"
                                                @selected((string) $selectedDashboardId === (string) $dashboard->id)>
                                                {{ $dashboard->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-muted">Debe estar permitido para el rol del usuario.</small>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('dashboard_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label d-block">Dashboards habilitados</label>
                                <div class="dashboard-checkbox-grid border rounded p-3">
                                    @foreach ($dashboards as $dashboard)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="dashboard_ids[]"
                                                id="dashboard_ids-{{ $user->id }}-{{ $dashboard->id }}"
                                                value="{{ $dashboard->id }}"
                                                @checked(in_array((string) $dashboard->id, $selectedDashboardIds, true))>
                                            <label class="form-check-label" for="dashboard_ids-{{ $user->id }}-{{ $dashboard->id }}">
                                                <i class="{{ $dashboard->icon }}"></i> {{ $dashboard->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Estos dashboards apareceran como botones para navegar en Inicio.</small>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('dashboard_ids')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                    @error('dashboard_ids.*')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label d-block">Alcance de inventario</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="puede_ver_todos_inventarios" value="0">
                                    <input class="form-check-input" type="checkbox" name="puede_ver_todos_inventarios"
                                        id="puede_ver_todos_inventarios-{{ $user->id }}" value="1"
                                        @checked($selectedPuedeVerTodosInventarios)>
                                    <label class="form-check-label" for="puede_ver_todos_inventarios-{{ $user->id }}">
                                        Puede ver todos los pañoles
                                    </label>
                                </div>
                                @if (session('open_modal') === 'editUserModal-' . $user->id)
                                    @error('puede_ver_todos_inventarios')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
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
        @endif

        @if ($canDeleteUsers && ! $isSuperUser)
            <div class="modal fade" id="deleteUserModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" style="color: white">Eliminar usuario</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-0">¿Está seguro de eliminar el usuario <strong>{{ $user->name }}</strong>?</p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
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

        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadCSVFromTable(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const csvRows = [];

            const headers = Array.from(table.querySelectorAll('thead th')).map(header => '"' + header.innerText.trim().replace(/"/g, '""') + '"');
            csvRows.push(headers.join(','));

            rows.forEach(row => {
                const cols = Array.from(row.querySelectorAll('td')).map(td => '"' + td.innerText.trim().replace(/"/g, '""') + '"');
                csvRows.push(cols.join(','));
            });

            downloadCSV(csvRows.join('\n'), filename);
        }

        function exportTableToExcel(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8" /></head><body>${table.outerHTML}</body></html>`;
            const uri = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.href = uri;
            link.download = filename + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function createPDF(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}h1{text-align:center;margin-bottom:1rem;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>' + filename + '</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write('<h1>' + filename.replace(/_/g, ' ') + '</h1>');
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }

        function printTable(tableId) {
            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const style = '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dee2e6;padding:.75rem;text-align:left;}th{background:#f8f9fa;}</style>';
            const newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Imprimir</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }
    </script>
@endpush
