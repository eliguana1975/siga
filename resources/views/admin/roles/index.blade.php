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

        .roles-permissions-cell {
            max-width: 620px;
            white-space: normal;
        }

        .roles-permissions-list {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
            max-height: 6.4rem;
            overflow: auto;
        }

        .roles-permission-pill {
            display: inline-flex;
            align-items: center;
            max-width: 240px;
            padding: .16rem .5rem;
            border: 1px solid var(--bs-border-color);
            border-radius: .25rem;
            color: var(--bs-body-color);
            background: transparent;
            font-size: .84rem;
            font-weight: 600;
            line-height: 1.35;
            white-space: nowrap;
        }

        .roles-permission-pill span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .roles-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .5rem .9rem;
        }
    </style>
@endpush

@section('content')
    @php
        $canCreateRoles = auth()->user()?->can('roles.crear');
        $canEditRoles = auth()->user()?->can('roles.editar');
        $canDeleteRoles = auth()->user()?->can('roles.eliminar');
        $showRoleActions = $canEditRoles || $canDeleteRoles;
    @endphp

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestión de Roles</h3>
                <p class="text-subtitle text-muted">
                    Administra los roles y los permisos asignados a cada uno registrado en el sistema.
                </p>
            </div>
            @if ($canCreateRoles)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                    <i class="bi bi-plus-circle"></i> Nuevo rol
                </button>
            @endif
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de roles registrados</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="{{ $search ?? request('search') }}" placeholder="Escribe el nombre del rol">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-light-secondary w-100">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-12 d-flex flex-column flex-md-row gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="exportTableToExcel('rolesTable', 'roles_registrados')">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="downloadCSVFromTable('rolesTable', 'roles_registrados')">
                                <i class="bi bi-file-earmark-arrow-down"></i> Exportar CSV
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="createPDF('rolesTable', 'roles_registrados')">
                                <i class="bi bi-file-earmark-pdf"></i> Crear PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 w-md-auto"
                                onclick="printTable('rolesTable')">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    @if (!empty($search ?? request('search')))
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron {{ $roles->total() }} resultado(s) para "{{ $search ?? request('search') }}".
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="rolesTable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre del rol</th>
                                    <th>Dashboards</th>
                                    <th>Permisos</th>
                                    @if ($showRoleActions)
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $roles->firstItem() + $loop->index }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @php
                                                $dashboardIdsForRole = $roleDashboardIds->get($role->id, []);
                                                $dashboardsForRole = $dashboards->whereIn('id', $dashboardIdsForRole);
                                            @endphp
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse ($dashboardsForRole as $dashboard)
                                                    <span class="badge bg-light-primary">
                                                        <i class="{{ $dashboard->icon }}"></i> {{ $dashboard->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted">Sin dashboards</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="roles-permissions-cell">
                                            @forelse ($role->permissions->sortBy(fn ($permission) => $permissionLabels[$permission->name] ?? $permission->name) as $permission)
                                                @if ($loop->first)
                                                    <div class="roles-permissions-list">
                                                @endif
                                                    <span class="roles-permission-pill"
                                                        title="{{ $permissionLabels[$permission->name] ?? $permission->name }}">
                                                        <span>{{ $permissionLabels[$permission->name] ?? $permission->name }}</span>
                                                    </span>
                                                @if ($loop->last)
                                                    </div>
                                                @endif
                                            @empty
                                                <span class="text-muted">Sin permisos</span>
                                            @endforelse
                                        </td>
                                        @if ($showRoleActions)
                                            <td class="text-end">
                                                @if ($canEditRoles)
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#editRoleModal-{{ $role->id }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                @endif

                                                @if ($canDeleteRoles)
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteRoleModal-{{ $role->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showRoleActions ? 5 : 4 }}" class="text-center text-muted py-4">No hay roles registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($roles->count() > 0)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando {{ $roles->firstItem() }} a {{ $roles->lastItem() }} de {{ $roles->total() }}
                                registros
                            </small>
                            <div>
                                {{ $roles->links('vendor.pagination.bootstrap-5-no-summary') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @if ($canCreateRoles)
        <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" style="color: white">Crear rol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del rol (*)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                                placeholder="Nombre del rol" required>
                        </div>
                        @if (session('open_modal') === 'createRoleModal')
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dashboards habilitados</label>
                        <div class="roles-dashboard-grid border rounded p-3">
                            @php
                                $oldDashboardIds = collect(old('dashboard_ids', []))->map(fn ($id) => (string) $id)->all();
                            @endphp
                            @foreach ($dashboards as $dashboard)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dashboard_ids[]"
                                        value="{{ $dashboard->id }}" id="create_dashboard_{{ $dashboard->id }}"
                                        @checked(in_array((string) $dashboard->id, $oldDashboardIds, true))>
                                    <label class="form-check-label" for="create_dashboard_{{ $dashboard->id }}">
                                        <i class="{{ $dashboard->icon }}"></i> {{ $dashboard->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Los usuarios con este rol podran navegar esos dashboards en Inicio.</small>
                        @if (session('open_modal') === 'createRoleModal')
                            @error('dashboard_ids')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                            @error('dashboard_ids.*')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Asignar permisos</label>
                        @foreach ($groupedPermissions as $groupName => $groupPermissions)
                            <h6 class="mt-3">{{ $groupName }}</h6>
                            <div class="row">
                                @foreach ($groupPermissions as $permission)
                                    <div class="col-12 col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="{{ $permission->id }}" id="create_perm_{{ $permission->id }}"
                                                @checked(in_array((string) $permission->id, old('permissions', []), true))>
                                            <label class="form-check-label" for="create_perm_{{ $permission->id }}">
                                                {{ $permissionLabels[$permission->name] ?? $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (session('open_modal') === 'createRoleModal')
                            @error('permissions')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            @error('permissions.*')
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
    @endif

    @foreach ($roles as $role)
        @php
            $selectedPermissions = $role->permissions->pluck('id')->map(fn ($id) => (int) $id)->all();

            if (session('open_modal') === 'editRoleModal-' . $role->id && is_array(old('permissions'))) {
                $selectedPermissions = array_map('intval', old('permissions'));
            }

            $selectedDashboardIds = $roleDashboardIds->get($role->id, []);

            if (session('open_modal') === 'editRoleModal-' . $role->id && is_array(old('dashboard_ids'))) {
                $selectedDashboardIds = array_map('intval', old('dashboard_ids'));
            }
        @endphp

        @if ($canEditRoles)
            <div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name-{{ $role->id }}" class="form-label">Nombre del rol (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                                <input type="text" name="name" id="name-{{ $role->id }}" class="form-control"
                                    value="{{ session('open_modal') === 'editRoleModal-' . $role->id ? old('name', $role->name) : $role->name }}"
                                    placeholder="Nombre del rol" required>
                            </div>
                            @if (session('open_modal') === 'editRoleModal-' . $role->id)
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dashboards habilitados</label>
                            <div class="roles-dashboard-grid border rounded p-3">
                                @foreach ($dashboards as $dashboard)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dashboard_ids[]"
                                            value="{{ $dashboard->id }}"
                                            id="edit_dashboard_{{ $role->id }}_{{ $dashboard->id }}"
                                            @checked(in_array((int) $dashboard->id, $selectedDashboardIds, true))>
                                        <label class="form-check-label" for="edit_dashboard_{{ $role->id }}_{{ $dashboard->id }}">
                                            <i class="{{ $dashboard->icon }}"></i> {{ $dashboard->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Los usuarios con este rol podran navegar esos dashboards en Inicio.</small>
                            @if (session('open_modal') === 'editRoleModal-' . $role->id)
                                @error('dashboard_ids')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                                @error('dashboard_ids.*')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Asignar permisos</label>
                            @foreach ($groupedPermissions as $groupName => $groupPermissions)
                                <h6 class="mt-3">{{ $groupName }}</h6>
                                <div class="row">
                                    @foreach ($groupPermissions as $permission)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    id="edit_perm_{{ $role->id }}_{{ $permission->id }}"
                                                    @checked(in_array((int) $permission->id, $selectedPermissions, true))>
                                                <label class="form-check-label"
                                                    for="edit_perm_{{ $role->id }}_{{ $permission->id }}">
                                                    {{ $permissionLabels[$permission->name] ?? $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            @if (session('open_modal') === 'editRoleModal-' . $role->id)
                                @error('permissions')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                @error('permissions.*')
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
        @endif

        @if ($canDeleteRoles)
            <div class="modal fade" id="deleteRoleModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('admin.roles.destroy', $role->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">¿Está seguro de eliminar el rol <strong>{{ $role->name }}</strong>?</p>
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
            newWindow.document.write('<h1>Roles registrados</h1>');
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
            newWindow.document.write('<html><head><title>Imprimir roles</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style>' + '</head><body>' + window.sigaPrintCompanyHeader());
            newWindow.document.write(table.outerHTML);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
        }
    </script>
@endpush
