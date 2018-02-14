<div class="row" id="{{ $grid->getId() }}">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                @if($grid->wantsPagination())
                    <div class="pull-right">
                        <b>
                            @if($grid->getData()->total() <= $grid->getData()->perpage())
                                Showing {{ ($grid->getData()->currentpage() -1 ) * $grid->getData()->perpage() + 1 }}
                                to {{ $grid->getData()->total() }}
                                of {{ $grid->getData()->total() }} entries.
                            @else
                                Showing {{ ($grid->getData()->currentpage() -1 ) * $grid->getData()->perpage() + 1 }}
                                to {{ $grid->getData()->currentpage() * $grid->getData()->perpage() }}
                                of {{ $grid->getData()->total() }} entries.
                            @endif
                        </b>
                    </div>
                @else
                    <div class="pull-right">
                        <b>
                            showing {{ $grid->getData()->count() }} records.
                        </b>
                    </div>
                @endif
                <h3 class="panel-title">{{ $grid->getName() }}</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    {!! $grid->getSearch() !!}
                    <div class="col-md-{{ $grid->getToolbarSize()[1] }}">
                        <div class="pull-right">
                            @foreach($grid->getButtons('toolbar') as $button)
                                {!! $button->render() !!}
                            @endforeach
                        </div>
                    </div>
                </div>
                <hr>
                <form action="{{ $grid->getSearchRoute() }}" method="GET" id="{{ $grid->getFilterFormId() }}"></form>
                <table class="{{ $grid->getClass() }}">
                    <thead>
                        <!-- headers -->
                        <tr>
                            @foreach($rows as $row)

                                @if($loop->first)

                                    @if($sort = $row->sortable)
                                        @if(is_callable($grid->getSortUrl()))
                                            <th class="{{ $row->headerClass }}" title="click to sort by {{ $row->key }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ call_user_func($grid->getSortUrl(), $row->key) }}">
                                                    {{ $row->name }}
                                                </a>
                                            </th>
                                        @else
                                            <th class="{{ $row->headerClass }}" title="click to sort by {{ $row->key }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ route($grid->getSortUrl(), add_query_param([$this->getSortParam() => $row->key])) }}">
                                                    {{ $row->name }}
                                                </a>
                                            </th>
                                        @endif
                                    @else
                                        <th class="{{ $row->headerClass }}">
                                            {{ $row->name }}
                                        </th>
                                    @endif
                                @else
                                    @if($sort = $row->sortable)
                                        @if(is_callable($grid->getSortUrl()))
                                            <th title="click to sort by {{ $row->key }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ call_user_func($grid->getSortUrl(), $row->key) }}">
                                                    {{ $row->name }}
                                                </a>
                                            </th>
                                        @else
                                            <th title="click to sort by {{ $row->key }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ route($grid->getSortUrl(), add_query_param([$this->getSortParam() => $row->key])) }}">
                                                    {{ $row->name }}
                                                </a>
                                            </th>
                                        @endif
                                    @else
                                        <th class="{{ $row->headerClass }}">
                                            {{ $row->name }}
                                        </th>
                                    @endif
                                @endif
                            @endforeach
                            <th></th>
                        </tr>
                        <!-- end headers -->
                        <!-- filter -->
                        <tr>
                            @include('leantony::grid.filter', ['rows' => $rows, 'formId' => $grid->getFilterFormId()])
                        </tr>
                        <!-- end filters -->
                    </thead>
                    <!-- data -->
                    <tbody>
                    @if($grid->hasItems())
                        @if($grid->warnIfEmpty())
                            <div class="alert alert-warning">
                                <strong><i class="fa fa-exclamation-triangle"></i>&nbsp;No data present!.</strong>
                            </div>
                        @endif
                    @else
                        @if($grid->skipsDefaultRowFormat())
                            @include($grid->getRowsView(), [$grid->getDataVariableAlias() => $item, 'grid' => $grid])
                        @else
                            @foreach($grid->getData() as $item)
                                @if($grid->allowsLinkableRows())
                                    @php
                                        $callback = call_user_func($grid->getLinkableCallback(), $grid->transformName(), $item);
                                    @endphp
                                    <tr class="linkable" data-url="{{ $callback }}">
                                @else
                                    <tr>
                                        @endif
                                        @foreach($rows as $row)
                                            @if(is_callable($row->data))
                                                @if($row->raw)
                                                    <td class="{{ $row->rowClass }}">
                                                        {!! call_user_func($row->data, $item, $row->key) !!}
                                                    </td>
                                                @else
                                                    <td class="{{ $row->rowClass }}">
                                                        {{ call_user_func($row->data , $item, $row->key) }}
                                                    </td>
                                                @endif
                                            @else
                                                @if($row->raw)
                                                    <td class="{{ $row->rowClass }}">
                                                        {!! $item->{$row->key} !!}
                                                    </td>
                                                @else
                                                    <td class="{{ $row->rowClass }}">
                                                        {{ $item->{$row->key} }}
                                                    </td>
                                                @endif
                                            @endif
                                            @if($loop->last)
                                                <td>
                                                    <div class="pull-right">
                                                        @foreach($grid->getButtons('rows') as $button)
                                                            {!! $button->render(call_user_func($button->getUrlRenderer(), $grid->transformName(), $item, $row->key)) !!}
                                                        @endforeach
                                                    </div>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                    @endforeach
                                @endif
                                @endif
                    </tbody>
                    <!-- end data -->
                </table>
                <!-- pagination -->
                @if($grid->wantsPagination())
                    <hr>
                    <div class="center">
                        {{ $grid->getData()->appends(request()->query())->links('leantony::grid.pagination.default', ['pjaxTarget' => $grid->getId()]) }}
                    </div><!-- /.center -->
                @endif
                <!-- end pagination -->
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        (function ($) {
            var grid = "{{ '#' . $grid->getId() }}";
            var filterForm = "{{ '#' . $grid->getFilterFormId() }}";
            var searchForm = "{{ '#' . $grid->getSearchFormId() }}";
            _grid({
                id: grid,
                filterForm: filterForm,
                dateRangeSelector: '.date-range',
                searchForm: searchForm,
                pjax: {
                    pjaxOptions: {}
                },
                linkables: {
                    element: '.linkable',
                    url: 'url',
                    timeout: 100
                }
            });
        })(jQuery);

    </script>
@endpush