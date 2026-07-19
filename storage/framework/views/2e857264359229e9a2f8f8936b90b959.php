<?php if($paginator->hasPages()): ?>
    <?php
        $maxVisiblePages = 3;
        $currentPage = $paginator->currentPage();
        $totalPages = $paginator->lastPage();
        $firstPage = max(1, $currentPage - intdiv($maxVisiblePages, 2));
        $lastPage = min($totalPages, $firstPage + $maxVisiblePages - 1);

        if ($lastPage - $firstPage + 1 < $maxVisiblePages) {
            $firstPage = max(1, $lastPage - $maxVisiblePages + 1);
        }
    ?>

    <nav class="d-flex justify-items-center justify-content-between" aria-label="Navegacion de paginas">
        <ul class="pagination mb-0">
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled" aria-disabled="true" aria-label="Anterior">
                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev"
                        aria-label="Anterior">&lsaquo;</a>
                </li>
            <?php endif; ?>

            <?php for($page = $firstPage; $page <= $lastPage; $page++): ?>
                <?php if($page == $currentPage): ?>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link"><?php echo e($page); ?></span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo e($paginator->url($page)); ?>"><?php echo e($page); ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next"
                        aria-label="Siguiente">&rsaquo;</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled" aria-disabled="true" aria-label="Siguiente">
                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH C:\wamp64\www\siga\resources\views/vendor/pagination/bootstrap-5-no-summary.blade.php ENDPATH**/ ?>