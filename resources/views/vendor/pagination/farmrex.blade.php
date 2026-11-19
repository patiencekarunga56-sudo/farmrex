@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <span class="muted">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="pages">
            @if ($paginator->onFirstPage())
                <span class="disabled">&laquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="disabled">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="current">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
            @else
                <span class="disabled">&raquo;</span>
            @endif
        </div>
    </nav>
@endif
