<div class="table-responsive">
    <table class="table table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Interno</th>
                <th>Dominio</th>
                <th>Vehiculo</th>
                <th>Medidor actual</th>
                <th>Sistema</th>
                <th>Servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $serviciosParaRealizar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servicio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($servicio['interno']); ?></td>
                    <td><?php echo e($servicio['dominio']); ?></td>
                    <td><?php echo e($servicio['vehiculo'] ?: '-'); ?></td>
                    <td><?php echo e(number_format((int) ($servicio['lectura_actual'] ?? 0), 0, ',', '.')); ?> <?php echo e(($servicio['unidad'] ?? 'km') === 'horas' ? 'hs' : 'km'); ?></td>
                    <td><?php echo e($servicio['sistema']); ?></td>
                    <td><?php echo e($servicio['servicio']); ?></td>
                    <td><span class="badge bg-light-danger">Realizar</span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay internos con servicios vencidos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\wamp64\www\siga\resources\views/admin/partials/dashboard-servicios.blade.php ENDPATH**/ ?>