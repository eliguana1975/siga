@if ($paginator->hasPages())
    @php
        $maxVisiblePages = 3;
        $currentPage = $paginator->currentPage();
        $totalPages = $paginator->lastPage();
        $firstPage = max(1, $currentPage - intdiv($maxVisiblePages, 2));
        $lastPage = min($totalPages, $firstPage + $maxVisiblePages - 1);

        if ($lastPage - $firstPage + 1 < $maxVisiblePages) {
            $firstPage = max(1, $lastPage - $maxVisiblePages + 1);
        }
    @endphp

    <nav class="d-flex justify-items-center justify-content-between" aria-label="Navegacion de paginas">
        <ul class="pagination mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Anterior">
                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                        aria-label="Anterior">&lsaquo;</a>
                </li>
            @endif

            @for ($page = $firstPage; $page <= $lastPage; $page++)
                @if ($page == $currentPage)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                        aria-label="Siguiente">&rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Siguiente">
                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
