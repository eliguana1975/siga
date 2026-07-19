<?php $__env->startPush('styles'); ?>
    <style>
        .dashboard-panel-scroll {
            max-height: 210px;
            overflow-y: auto;
            padding-right: .35rem;
        }

        .dashboard-panel-scroll::-webkit-scrollbar {
            width: 7px;
        }

        .dashboard-panel-scroll::-webkit-scrollbar-thumb {
            background: rgba(151, 164, 255, .45);
            border-radius: 999px;
        }

        .dashboard-panel-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .05);
            border-radius: 999px;
        }

        .dashboard-stat-chart {
            min-height: 250px;
        }

        .dashboard-chart-empty {
            min-height: 250px;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $activeDashboardKey = $activeDashboard?->key ?? null;
    ?>

    <div class="page-heading">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h3><?php echo e($activeDashboard?->name ?? 'Dashboard'); ?></h3>
                <p class="text-subtitle text-muted mb-0">
                    <?php echo e($activeDashboard?->description ?? 'Resumen general del sistema.'); ?>

                </p>
            </div>
            <?php if(($availableDashboards ?? collect())->count() > 1): ?>
                <div class="d-flex flex-wrap gap-2 align-items-start">
                    <?php $__currentLoopData = $availableDashboards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dashboard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('admin.index', ['dashboard' => $dashboard->key])); ?>"
                            class="btn btn-sm <?php echo e($activeDashboardKey === $dashboard->key ? 'btn-primary' : 'btn-outline-primary'); ?>">
                            <i class="<?php echo e($dashboard->icon); ?>"></i> <?php echo e($dashboard->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="page-content">
        <?php if(! $activeDashboard): ?>
            <div class="alert alert-warning">
                No tienes dashboards asignados. Solicita al superusuario que habilite un dashboard para tu rol.
            </div>
        <?php elseif($activeDashboardKey === 'choferes'): ?>
            <section class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-person-vcard fs-1 text-muted"></i>
                            <h4 class="mt-3">Dashboard de choferes</h4>
                            <p class="text-muted mb-0">Sin indicadores configurados por ahora.</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php elseif($activeDashboardKey === 'compras-inventario'): ?>
            <section class="row">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Stock critico</h6><h4 class="font-extrabold mb-0"><?php echo e($operationalAlerts['counts']['stock_critico'] ?? 0); ?></h4></div></div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Solicitudes demoradas</h6><h4 class="font-extrabold mb-0"><?php echo e($operationalAlerts['counts']['solicitudes_demoradas'] ?? 0); ?></h4></div></div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Reparaciones vencidas</h6><h4 class="font-extrabold mb-0"><?php echo e($operationalAlerts['counts']['reparaciones_vencidas'] ?? 0); ?></h4></div></div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Alertas totales</h6><h4 class="font-extrabold mb-0"><?php echo e($operationalAlerts['counts']['total'] ?? 0); ?></h4></div></div>
                </div>
            </section>

            <section class="row">
                <?php
                    $purchaseChartCards = [
                        [
                            'id' => 'purchase-alerts-chart',
                            'title' => 'Alertas por tipo',
                            'subtitle' => 'Distribucion de alertas activas.',
                            'data' => $purchaseInventoryCharts['alertas'] ?? ['labels' => [], 'series' => []],
                            'type' => 'donut',
                        ],
                        [
                            'id' => 'critical-stock-deposit-chart',
                            'title' => 'Stock critico por deposito',
                            'subtitle' => 'Articulos debajo del minimo por deposito.',
                            'data' => $purchaseInventoryCharts['stock_por_deposito'] ?? ['labels' => [], 'series' => []],
                            'type' => 'bar',
                        ],
                        [
                            'id' => 'requests-status-chart',
                            'title' => 'Solicitudes por estado',
                            'subtitle' => 'Estado actual de solicitudes de repuestos.',
                            'data' => $purchaseInventoryCharts['solicitudes_por_estado'] ?? ['labels' => [], 'series' => []],
                            'type' => 'bar',
                        ],
                        [
                            'id' => 'repairs-status-chart',
                            'title' => 'Reparaciones por estado',
                            'subtitle' => 'Seguimiento de reparaciones de articulos.',
                            'data' => $purchaseInventoryCharts['reparaciones_por_estado'] ?? ['labels' => [], 'series' => []],
                            'type' => 'donut',
                        ],
                    ];
                ?>

                <?php $__currentLoopData = $purchaseChartCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chartCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title mb-0"><?php echo e($chartCard['title']); ?></h4>
                                <p class="text-muted mb-0"><?php echo e($chartCard['subtitle']); ?></p>
                            </div>
                            <div class="card-body">
                                <?php if(array_sum($chartCard['data']['series']) > 0): ?>
                                    <div id="<?php echo e($chartCard['id']); ?>" class="dashboard-stat-chart"></div>
                                <?php else: ?>
                                    <div class="dashboard-chart-empty d-flex align-items-center justify-content-center text-muted border rounded">
                                        Sin datos para graficar.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>

            <section class="row">
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Stock critico</h4></div>
                        <div class="card-body">
                            <div class="dashboard-panel-scroll">
                                <?php $__empty_1 = true; $__currentLoopData = ($operationalAlerts['stock_critico'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="border-bottom py-2">
                                        <div class="fw-semibold"><?php echo e($alerta['articulo']); ?></div>
                                        <small class="text-muted"><?php echo e($alerta['deposito']); ?> - Stock <?php echo e($alerta['cantidad']); ?> / min <?php echo e($alerta['stock_minimo']); ?></small>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-muted">Sin stock critico.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Solicitudes demoradas</h4></div>
                        <div class="card-body">
                            <div class="dashboard-panel-scroll">
                                <?php $__empty_1 = true; $__currentLoopData = ($operationalAlerts['solicitudes_demoradas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="border-bottom py-2">
                                        <div class="fw-semibold">#<?php echo e($alerta['id']); ?> - <?php echo e($alerta['descripcion']); ?></div>
                                        <small class="text-muted"><?php echo e($alerta['estado']); ?> - <?php echo e($alerta['dias_abierta'] ?? '-'); ?> dia(s) abierta</small>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-muted">Sin solicitudes demoradas.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header"><h4 class="card-title mb-0">Reparaciones vencidas</h4></div>
                        <div class="card-body">
                            <div class="dashboard-panel-scroll">
                                <?php $__empty_1 = true; $__currentLoopData = ($operationalAlerts['reparaciones_vencidas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="border-bottom py-2">
                                        <div class="fw-semibold"><?php echo e($alerta['numero_orden']); ?></div>
                                        <small class="text-muted"><?php echo e($alerta['proveedor'] ?: 'Sin proveedor'); ?> - <?php echo e($alerta['dias_vencida'] ?? '-'); ?> dia(s) vencida</small>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-muted">Sin reparaciones vencidas.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php elseif($activeDashboardKey === 'taller'): ?>
            <section class="row">
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Pendientes</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['pendiente']); ?></h4></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">En proceso</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['en_proceso']); ?></h4></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Completadas</h6><h4 class="font-extrabold mb-0"><?php echo e($workOrderStats['completada']); ?></h4></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card"><div class="card-body px-4 py-4"><h6 class="text-muted font-semibold">Vehiculos parados</h6><h4 class="font-extrabold mb-0"><?php echo e($vehicleStoppedStats['total'] ?? 0); ?></h4></div></div></div>
            </section>

            <section class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0">Internos para realizar servicio</h4>
                                <p class="text-muted mb-0">Servicios vencidos por kilometraje u horas.</p>
                            </div>
                            <a href="<?php echo e(route('admin.servicios-kilometraje.index')); ?>" class="btn btn-primary">
                                <i class="bi bi-speedometer2"></i> Ver control completo
                            </a>
                        </div>
                        <div class="card-body">
                            <?php echo $__env->make('admin.partials.dashboard-servicios', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <?php echo $__env->make('admin.partials.dashboard-general', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    </div>

    <?php if($activeDashboardKey === 'compras-inventario'): ?>
        <?php $__env->startPush('scripts'); ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof ApexCharts === 'undefined') {
                        return;
                    }

                    const charts = [
                        {
                            id: 'purchase-alerts-chart',
                            type: 'donut',
                            labels: <?php echo json_encode($purchaseInventoryCharts['alertas']['labels'] ?? [], 15, 512) ?>,
                            series: <?php echo json_encode($purchaseInventoryCharts['alertas']['series'] ?? [], 15, 512) ?>,
                        },
                        {
                            id: 'critical-stock-deposit-chart',
                            type: 'bar',
                            labels: <?php echo json_encode($purchaseInventoryCharts['stock_por_deposito']['labels'] ?? [], 15, 512) ?>,
                            series: <?php echo json_encode($purchaseInventoryCharts['stock_por_deposito']['series'] ?? [], 15, 512) ?>,
                        },
                        {
                            id: 'requests-status-chart',
                            type: 'bar',
                            labels: <?php echo json_encode($purchaseInventoryCharts['solicitudes_por_estado']['labels'] ?? [], 15, 512) ?>,
                            series: <?php echo json_encode($purchaseInventoryCharts['solicitudes_por_estado']['series'] ?? [], 15, 512) ?>,
                        },
                        {
                            id: 'repairs-status-chart',
                            type: 'donut',
                            labels: <?php echo json_encode($purchaseInventoryCharts['reparaciones_por_estado']['labels'] ?? [], 15, 512) ?>,
                            series: <?php echo json_encode($purchaseInventoryCharts['reparaciones_por_estado']['series'] ?? [], 15, 512) ?>,
                        },
                    ];

                    const colors = ['#435ebe', '#ffcf3f', '#ff5b5c', '#00cfe8', '#39da8a', '#9694ff', '#fdac41', '#a3e635'];

                    charts.forEach(function (chart) {
                        const element = document.getElementById(chart.id);

                        if (!element || !chart.series.some(function (value) { return Number(value) > 0; })) {
                            return;
                        }

                        const common = {
                            chart: {
                                type: chart.type,
                                height: 250,
                                toolbar: { show: false },
                                foreColor: '#c7d2fe',
                            },
                            colors: colors,
                            tooltip: {
                                y: {
                                    formatter: function (value) {
                                        return Number(value).toLocaleString('es-AR');
                                    },
                                },
                            },
                        };

                        const options = chart.type === 'bar'
                            ? {
                                ...common,
                                series: [{ name: 'Total', data: chart.series.map(Number) }],
                                xaxis: { categories: chart.labels },
                                plotOptions: {
                                    bar: {
                                        borderRadius: 4,
                                        columnWidth: '48%',
                                    },
                                },
                                dataLabels: { enabled: false },
                            }
                            : {
                                ...common,
                                labels: chart.labels,
                                series: chart.series.map(Number),
                                legend: {
                                    position: 'bottom',
                                    fontSize: '12px',
                                },
                                dataLabels: { enabled: false },
                                stroke: {
                                    width: 2,
                                    colors: ['#1f1d2f'],
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '68%',
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    label: 'Total',
                                                },
                                            },
                                        },
                                    },
                                },
                            };

                        new ApexCharts(element, options).render();
                    });
                });
            </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\siga\resources\views/admin/index.blade.php ENDPATH**/ ?>