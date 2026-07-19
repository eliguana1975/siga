<section class="row">
    <div class="col-12 col-lg-4">
        <div class="row">
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Total de vehiculos</h6><h4 class="font-extrabold mb-0"><?php echo e($fleetStats['total']); ?></h4></div></div></div>
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Vehiculos activos</h6><h4 class="font-extrabold mb-0"><?php echo e($fleetStats['activo']); ?></h4></div></div></div>
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Vehiculos de baja</h6><h4 class="font-extrabold mb-0"><?php echo e($fleetStats['baja']); ?></h4></div></div></div>
            <?php if($fleetStats['mantenimiento'] > 0): ?>
                <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">En mantenimiento</h6><h4 class="font-extrabold mb-0"><?php echo e($fleetStats['mantenimiento']); ?></h4></div></div></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Estado de la flota</h4></div>
            <div class="card-body"><div id="fleet-status-chart"></div></div>
        </div>
    </div>
</section>

<section class="row">
    <div class="col-12 col-lg-4">
        <div class="row">
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Pendientes</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['pendiente']); ?></h4></div></div></div>
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">En proceso</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['en_proceso']); ?></h4></div></div></div>
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Completadas</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['completada']); ?></h4></div></div></div>
            <div class="col-12 col-sm-6 col-lg-12"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Canceladas</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['cancelada']); ?></h4></div></div></div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Estado de ordenes de trabajo</h4></div>
            <div class="card-body"><div id="work-order-status-chart"></div></div>
        </div>
    </div>
</section>

<section class="row">
    <div class="col-12 col-lg-5"><div class="card"><div class="card-header"><h4 class="card-title mb-0">Vehiculos parados por motivo</h4></div><div class="card-body"><div id="vehicle-stopped-chart"></div></div></div></div>
    <div class="col-12 col-lg-7"><div class="card"><div class="card-header"><h4 class="card-title mb-0">Vehiculos parados por servicio asignado</h4></div><div class="card-body"><div id="service-assigned-chart"></div></div></div></div>
</section>

<section class="row">
    <div class="col-12"><div class="card"><div class="card-header"><h4 class="card-title mb-0">Distribucion por tipo de vehiculo</h4></div><div class="card-body"><div id="vehicle-type-chart"></div></div></div></div>
</section>

<section class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h4 class="card-title mb-0">Internos para realizar servicio</h4><p class="text-muted mb-0">Servicios vencidos por kilometraje u horas.</p></div>
                <a href="<?php echo e(route('admin.servicios-kilometraje.index')); ?>" class="btn btn-primary"><i class="bi bi-speedometer2"></i> Ver control completo</a>
            </div>
            <div class="card-body"><?php echo $__env->make('admin.partials.dashboard-servicios', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
        </div>
    </div>
</section>

<section class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Vencimientos proximos</h4><p class="text-muted mb-0">Verificaciones tecnicas que vencen dentro de los proximos 10 dias.</p></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="vencimientos-verificaciones-table" class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Interno</th>
                                <th>Dominio</th>
                                <th>Vehiculo</th>
                                <th>Verificacion</th>
                                <th>Vencimiento</th>
                                <th>Faltan</th>
                                <th>Comprobante</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $vencimientosVerificaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vencimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($vencimiento['interno']); ?></td>
                                    <td><?php echo e($vencimiento['dominio']); ?></td>
                                    <td><?php echo e($vencimiento['vehiculo'] ?: '-'); ?></td>
                                    <td><?php echo e($vencimiento['tipo']); ?></td>
                                    <td><?php echo e($vencimiento['fecha_vencimiento']?->format('d/m/Y')); ?></td>
                                    <td><?php echo e($vencimiento['dias_restantes']); ?> dia(s)</td>
                                    <td><?php echo e($vencimiento['comprobante'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($vencimiento['dias_restantes'] <= 3 ? 'bg-light-danger' : 'bg-light-warning'); ?>">
                                            Proximo
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay verificaciones tecnicas por vencer en los proximos 10 dias.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Carnets de conducir por vencer</h4><p class="text-muted mb-0">Empleados activos con carnet que vence dentro de los proximos 10 dias.</p></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="vencimientos-carnets-table" class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th>Vencimiento</th>
                                <th>Faltan</th>
                                <th>Telefono</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $vencimientosCarnetsConducir; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vencimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($vencimiento['empleado']); ?></td>
                                    <td><?php echo e($vencimiento['tipo_empleado'] ?: '-'); ?></td>
                                    <td><?php echo e($vencimiento['categoria_carnet'] ?: '-'); ?></td>
                                    <td><?php echo e($vencimiento['fecha_vencimiento']?->format('d/m/Y')); ?></td>
                                    <td><?php echo e($vencimiento['dias_restantes']); ?> dia(s)</td>
                                    <td><?php echo e($vencimiento['telefono'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($vencimiento['dias_restantes'] <= 3 ? 'bg-light-danger' : 'bg-light-warning'); ?>">
                                            Proximo
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay carnets de conducir por vencer en los proximos 10 dias.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Matafuegos por vencer</h4><p class="text-muted mb-0">Matafuegos/extintores cargados en ordenes de trabajo que vencen dentro de los proximos 30 dias.</p></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="vencimientos-matafuegos-table" class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>OT</th>
                                <th>Interno</th>
                                <th>Dominio</th>
                                <th>Articulo</th>
                                <th>Nro.</th>
                                <th>Vencimiento</th>
                                <th>Faltan</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $vencimientosMatafuegos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vencimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php if($vencimiento['orden_id']): ?>
                                            #<?php echo e($vencimiento['orden_id']); ?>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?php echo e($vencimiento['interno']); ?></td>
                                    <td><?php echo e($vencimiento['dominio']); ?></td>
                                    <td><?php echo e($vencimiento['articulo']); ?></td>
                                    <td><?php echo e($vencimiento['numero'] ?: '-'); ?></td>
                                    <td><?php echo e($vencimiento['fecha_vencimiento']?->format('d/m/Y')); ?></td>
                                    <td><?php echo e($vencimiento['dias_restantes']); ?> dia(s)</td>
                                    <td>
                                        <span class="badge <?php echo e($vencimiento['dias_restantes'] <= 7 ? 'bg-light-danger' : 'bg-light-warning'); ?>">
                                            Proximo
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay matafuegos por vencer en los proximos 30 dias.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
    $dashboardChartData = [
        'fleetStats' => $fleetStats,
        'workOrderStats' => $workOrderStats,
        'vehicleStoppedStats' => $vehicleStoppedStats,
        'serviceAssignedStats' => $serviceAssignedStats,
        'vehicleTypeStats' => $vehicleTypeStats,
    ];
?>

<script type="application/json" id="dashboard-chart-data"><?php echo json_encode($dashboardChartData, 15, 512) ?></script>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            const data = JSON.parse(document.getElementById('dashboard-chart-data')?.textContent || '{}');
            const charts = {
                'fleet-status-chart': {
                    labels: ['Activos', 'Baja', 'Mantenimiento'],
                    series: [data.fleetStats?.activo || 0, data.fleetStats?.baja || 0, data.fleetStats?.mantenimiento || 0],
                    colors: ['#198754', '#dc3545', '#435ebe']
                },
                'work-order-status-chart': {
                    labels: ['Pendientes', 'En proceso', 'Completadas', 'Canceladas'],
                    series: [data.workOrderStats?.pendiente || 0, data.workOrderStats?.en_proceso || 0, data.workOrderStats?.completada || 0, data.workOrderStats?.cancelada || 0],
                    colors: ['#f59e0b', '#435ebe', '#198754', '#6c757d']
                },
                'vehicle-stopped-chart': {
                    labels: data.vehicleStoppedStats?.labels || [],
                    series: data.vehicleStoppedStats?.series || [],
                    colors: ['#dc3545', '#f59e0b', '#435ebe', '#0dcaf0', '#6f42c1', '#6c757d']
                },
                'service-assigned-chart': {
                    labels: data.serviceAssignedStats?.labels || [],
                    series: data.serviceAssignedStats?.series || [],
                    colors: ['#198754']
                },
                'vehicle-type-chart': {
                    labels: data.vehicleTypeStats?.labels || [],
                    series: data.vehicleTypeStats?.series || [],
                    colors: ['#435ebe', '#198754', '#f59e0b', '#dc3545', '#0dcaf0', '#6f42c1', '#20c997', '#6c757d']
                },
            };

            Object.entries(charts).forEach(function([id, config]) {
                const element = document.getElementById(id);
                if (!element) {
                    return;
                }

                new ApexCharts(element, {
                    chart: { type: 'donut', height: 310, toolbar: { show: false } },
                    series: config.series.filter(value => Number(value) > 0),
                    labels: config.labels.filter((label, index) => Number(config.series[index] || 0) > 0),
                    colors: config.colors,
                    noData: { text: 'Sin datos' },
                    legend: { position: 'bottom' },
                }).render();
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\wamp64\www\siga\resources\views/admin/partials/dashboard-general.blade.php ENDPATH**/ ?>