@if ($paginator->hasPages())
<nav class="d-flex justify-content-center">
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <a href="#" class="page-link"><i class=fas fa-angle-left"></i></a>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" wire:click.prevent="previousPage" wire:loading.attr="disabled" href="#"><i class=fas fa-angle-left"></i></a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled"><span>{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <a class="page-link" href="#" wire:click.prevent="gotoPage({{ $page }})">{{ $page }}</a>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="#" wire:click.prevent="gotoPage({{ $page }})">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" wire:click.prevent="nextPage" wire:loading.attr="disabled" href="#"><i class=fas fa-angle-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
                <a href="#" class="page-link"><i class=fas fa-angle-right"></i></a>
            </li>
        @endif
    </ul>
</nav>
@endif
