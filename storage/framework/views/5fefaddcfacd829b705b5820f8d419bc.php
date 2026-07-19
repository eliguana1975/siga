<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $canCreateRoles = auth()->user()?->can('roles.crear');
        $canEditRoles = auth()->user()?->can('roles.editar');
        $canDeleteRoles = auth()->user()?->can('roles.eliminar');
        $showRoleActions = $canEditRoles || $canDeleteRoles;
    ?>

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Gestión de Roles</h3>
                <p class="text-subtitle text-muted">
                    Administra los roles y los permisos asignados a cada uno registrado en el sistema.
                </p>
            </div>
            <?php if($canCreateRoles): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                    <i class="bi bi-plus-circle"></i> Nuevo rol
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Listado de roles registrados</h4>
                </div>
                <div class="card-body">
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible show fade">
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="GET" action="<?php echo e(route('admin.roles.index')); ?>" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-8">
                                <label for="search" class="form-label mb-1">Buscar rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" id="search" class="form-control"
                                        value="<?php echo e($search ?? request('search')); ?>" placeholder="Escribe el nombre del rol">
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn btn-light-secondary w-100">Limpiar</a>
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

                    <?php if(!empty($search ?? request('search'))): ?>
                        <div class="alert alert-success py-2 mb-3" role="alert">
                            Se encontraron <?php echo e($roles->total()); ?> resultado(s) para "<?php echo e($search ?? request('search')); ?>".
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table id="rolesTable" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Nombre del rol</th>
                                    <th>Dashboards</th>
                                    <th>Permisos</th>
                                    <?php if($showRoleActions): ?>
                                        <th class="text-end" style="width: 220px;">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($roles->firstItem() + $loop->index); ?></td>
                                        <td><?php echo e($role->name); ?></td>
                                        <td>
                                            <?php
                                                $dashboardIdsForRole = $roleDashboardIds->get($role->id, []);
                                                $dashboardsForRole = $dashboards->whereIn('id', $dashboardIdsForRole);
                                            ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php $__empty_2 = true; $__currentLoopData = $dashboardsForRole; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dashboard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <span class="badge bg-light-primary">
                                                        <i class="<?php echo e($dashboard->icon); ?>"></i> <?php echo e($dashboard->name); ?>

                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <span class="text-muted">Sin dashboards</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="roles-permissions-cell">
                                            <?php $__empty_2 = true; $__currentLoopData = $role->permissions->sortBy(fn ($permission) => $permissionLabels[$permission->name] ?? $permission->name); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                <?php if($loop->first): ?>
                                                    <div class="roles-permissions-list">
                                                <?php endif; ?>
                                                    <span class="roles-permission-pill"
                                                        title="<?php echo e($permissionLabels[$permission->name] ?? $permission->name); ?>">
                                                        <span><?php echo e($permissionLabels[$permission->name] ?? $permission->name); ?></span>
                                                    </span>
                                                <?php if($loop->last): ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                <span class="text-muted">Sin permisos</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if($showRoleActions): ?>
                                            <td class="text-end">
                                                <?php if($canEditRoles): ?>
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#editRoleModal-<?php echo e($role->id); ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if($canDeleteRoles): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteRoleModal-<?php echo e($role->id); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="<?php echo e($showRoleActions ? 5 : 4); ?>" class="text-center text-muted py-4">No hay roles registrados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($roles->count() > 0): ?>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                            <small class="text-muted">
                                Mostrando <?php echo e($roles->firstItem()); ?> a <?php echo e($roles->lastItem()); ?> de <?php echo e($roles->total()); ?>

                                registros
                            </small>
                            <div>
                                <?php echo e($roles->links('vendor.pagination.bootstrap-5-no-summary')); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <?php if($canCreateRoles): ?>
        <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="<?php echo e(route('admin.roles.store')); ?>">
                <?php echo csrf_field(); ?>
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
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo e(old('name')); ?>"
                                placeholder="Nombre del rol" required>
                        </div>
                        <?php if(session('open_modal') === 'createRoleModal'): ?>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dashboards habilitados</label>
                        <div class="roles-dashboard-grid border rounded p-3">
                            <?php
                                $oldDashboardIds = collect(old('dashboard_ids', []))->map(fn ($id) => (string) $id)->all();
                            ?>
                            <?php $__currentLoopData = $dashboards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dashboard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dashboard_ids[]"
                                        value="<?php echo e($dashboard->id); ?>" id="create_dashboard_<?php echo e($dashboard->id); ?>"
                                        <?php if(in_array((string) $dashboard->id, $oldDashboardIds, true)): echo 'checked'; endif; ?>>
                                    <label class="form-check-label" for="create_dashboard_<?php echo e($dashboard->id); ?>">
                                        <i class="<?php echo e($dashboard->icon); ?>"></i> <?php echo e($dashboard->name); ?>

                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <small class="text-muted">Los usuarios con este rol podran navegar esos dashboards en Inicio.</small>
                        <?php if(session('open_modal') === 'createRoleModal'): ?>
                            <?php $__errorArgs = ['dashboard_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger d-block"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['dashboard_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger d-block"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Asignar permisos</label>
                        <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupPermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <h6 class="mt-3"><?php echo e($groupName); ?></h6>
                            <div class="row">
                                <?php $__currentLoopData = $groupPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-12 col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="<?php echo e($permission->id); ?>" id="create_perm_<?php echo e($permission->id); ?>"
                                                <?php if(in_array((string) $permission->id, old('permissions', []), true)): echo 'checked'; endif; ?>>
                                            <label class="form-check-label" for="create_perm_<?php echo e($permission->id); ?>">
                                                <?php echo e($permissionLabels[$permission->name] ?? $permission->name); ?>

                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(session('open_modal') === 'createRoleModal'): ?>
                            <?php $__errorArgs = ['permissions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['permissions.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
        </div>
    <?php endif; ?>

    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $selectedPermissions = $role->permissions->pluck('id')->map(fn ($id) => (int) $id)->all();

            if (session('open_modal') === 'editRoleModal-' . $role->id && is_array(old('permissions'))) {
                $selectedPermissions = array_map('intval', old('permissions'));
            }

            $selectedDashboardIds = $roleDashboardIds->get($role->id, []);

            if (session('open_modal') === 'editRoleModal-' . $role->id && is_array(old('dashboard_ids'))) {
                $selectedDashboardIds = array_map('intval', old('dashboard_ids'));
            }
        ?>

        <?php if($canEditRoles): ?>
            <div class="modal fade" id="editRoleModal-<?php echo e($role->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="<?php echo e(route('admin.roles.update', $role->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" style="color: white">Editar rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name-<?php echo e($role->id); ?>" class="form-label">Nombre del rol (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                                <input type="text" name="name" id="name-<?php echo e($role->id); ?>" class="form-control"
                                    value="<?php echo e(session('open_modal') === 'editRoleModal-' . $role->id ? old('name', $role->name) : $role->name); ?>"
                                    placeholder="Nombre del rol" required>
                            </div>
                            <?php if(session('open_modal') === 'editRoleModal-' . $role->id): ?>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dashboards habilitados</label>
                            <div class="roles-dashboard-grid border rounded p-3">
                                <?php $__currentLoopData = $dashboards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dashboard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dashboard_ids[]"
                                            value="<?php echo e($dashboard->id); ?>"
                                            id="edit_dashboard_<?php echo e($role->id); ?>_<?php echo e($dashboard->id); ?>"
                                            <?php if(in_array((int) $dashboard->id, $selectedDashboardIds, true)): echo 'checked'; endif; ?>>
                                        <label class="form-check-label" for="edit_dashboard_<?php echo e($role->id); ?>_<?php echo e($dashboard->id); ?>">
                                            <i class="<?php echo e($dashboard->icon); ?>"></i> <?php echo e($dashboard->name); ?>

                                        </label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <small class="text-muted">Los usuarios con este rol podran navegar esos dashboards en Inicio.</small>
                            <?php if(session('open_modal') === 'editRoleModal-' . $role->id): ?>
                                <?php $__errorArgs = ['dashboard_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['dashboard_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Asignar permisos</label>
                            <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupPermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <h6 class="mt-3"><?php echo e($groupName); ?></h6>
                                <div class="row">
                                    <?php $__currentLoopData = $groupPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-12 col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    value="<?php echo e($permission->id); ?>"
                                                    id="edit_perm_<?php echo e($role->id); ?>_<?php echo e($permission->id); ?>"
                                                    <?php if(in_array((int) $permission->id, $selectedPermissions, true)): echo 'checked'; endif; ?>>
                                                <label class="form-check-label"
                                                    for="edit_perm_<?php echo e($role->id); ?>_<?php echo e($permission->id); ?>">
                                                    <?php echo e($permissionLabels[$permission->name] ?? $permission->name); ?>

                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(session('open_modal') === 'editRoleModal-' . $role->id): ?>
                                <?php $__errorArgs = ['permissions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['permissions.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            </div>
            </div>
        <?php endif; ?>

        <?php if($canDeleteRoles): ?>
            <div class="modal fade" id="deleteRoleModal-<?php echo e($role->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="<?php echo e(route('admin.roles.destroy', $role->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" style="color: white">Eliminar rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">¿Está seguro de eliminar el rol <strong><?php echo e($role->name); ?></strong>?</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script data-open-modal="<?php echo e(session('open_modal')); ?>">
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\siga\resources\views/admin/roles/index.blade.php ENDPATH**/ ?>