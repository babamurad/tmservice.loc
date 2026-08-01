@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span class="muted">« Назад</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">« Назад</a>
    @endif

    <span class="muted">Стр. {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">Вперёд »</a>
    @else
        <span class="muted">Вперёд »</span>
    @endif
@endif
