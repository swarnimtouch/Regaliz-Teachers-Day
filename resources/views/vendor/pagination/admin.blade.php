@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Pagination">
        <p class="admin-pagination-info">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} records
        </p>
        <div class="admin-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="admin-page-link is-disabled" aria-disabled="true">‹</span>
            @else
                <a class="admin-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-page-link is-disabled">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="admin-page-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="admin-page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="admin-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">›</a>
            @else
                <span class="admin-page-link is-disabled" aria-disabled="true">›</span>
            @endif
        </div>
    </nav>
@endif
