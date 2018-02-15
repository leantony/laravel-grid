@if ($paginator->hasPages())
    <div class="row">
        <div class="col-md-4">
            <div class="pull-left">Showing {{ ($paginator->currentpage() -1 ) * $paginator->perpage() + 1 }}
                to {{ $paginator->currentpage() * $paginator->perpage() }}
                of {{ $paginator->total() }} entries.
            </div>
        </div>
        <div class="col-md-8">
            <div class="pull-right">
                <ul class="pagination" style="margin-top: 0">
                    @if ($paginator->onFirstPage())
                        <li class="disabled"><span>&laquo;</span></li>
                    @else
                        <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" data-trigger-pjax="1"
                               data-pjax-target="#{{ $pjaxTarget }}">&laquo;</a></li>
                    @endif
                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="disabled"><span>{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="active"><span>{{ $page }}</span></li>
                                @else
                                    <li><a href="{{ $url }}" data-trigger-pjax="1"
                                           data-pjax-target="#{{ $pjaxTarget }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" data-trigger-pjax="1"
                               data-pjax-target="#{{ $pjaxTarget }}">&raquo;</a></li>
                    @else
                        <li class="disabled"><span>&raquo;</span></li>
                    @endif
                </ul>
            </div>

        </div>
    </div>
@endif