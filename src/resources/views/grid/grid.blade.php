<div class="row" id="{{ $grid->getId() }}">
    <div class="col-md-12 col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="pull-left">
                    <h4 class="grid-title">{{ $grid->getName() }}</h4>
                </div>
                <!-- pagination info -->
                @if($grid->wantsPagination() && !$grid->needsSimplePagination())
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
                            Showing {{ $grid->getData()->count() }} records for this page.
                        </b>
                    </div>
            @endif
            <!-- end pagination info -->
            </div>
            <div class="card-body">
                <!-- search form -->
                <div class="row">
                {!! $grid->renderSearchForm() !!}
                <!-- toolbar buttons -->
                    @if($grid->hasButtons('toolbar'))
                        <div class="col-md-{{ $grid->getToolbarSize()[1] }}">
                            <div class="pull-right">
                                @foreach($grid->getButtons('toolbar') as $button)
                                    {!! $button->render() !!}
                                @endforeach
                            </div>
                        </div>
                @endif
                <!-- end toolbar buttons -->
                </div>
                <hr>
                <!-- end search form -->
                <!-- filter form declaration -->
                <form action="{{ $grid->getSearchRoute() }}" method="GET" id="{{ $grid->getFilterFormId() }}"></form>
                <!-- grid contents -->
                <div class="table-responsive">
                    <table class="{{ $grid->getClass() }}">
                        <thead>
                        <!-- headers -->
                        <tr>
                            @foreach($columns as $column)

                                @if($loop->first)

                                    @if($sort = $column->sortable)
                                        @if(is_callable($grid->getSortUrl()))
                                            <th scope="col" class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}"
                                                title="click to sort by {{ $column->key }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ call_user_func($grid->getSortUrl(), $column->key) }}">
                                                    {{ $column->name }}
                                                </a>
                                            </th>
                                        @else
                                            <th scope="col" class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}"
                                                title="click to sort by {{ $column->key }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ route($grid->getSortUrl(), add_query_param([$this->getSortParam() => $column->key])) }}">
                                                    {{ $column->name }}
                                                </a>
                                            </th>
                                        @endif
                                    @else
                                        <th class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
                                            {{ $column->name }}
                                        </th>
                                    @endif
                                @else
                                    @if($sort = $column->sortable)
                                        @if(is_callable($grid->getSortUrl()))
                                            <th scope="col" title="click to sort by {{ $column->key }}"
                                                class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ call_user_func($grid->getSortUrl(), $column->key) }}">
                                                    {{ $column->name }}
                                                </a>
                                            </th>
                                        @else
                                            <th scope="col" title="click to sort by {{ $column->key }}"
                                                class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
                                                <a data-trigger-pjax="1" class="data-sort"
                                                   href="{{ route($grid->getSortUrl(), add_query_param([$this->getSortParam() => $column->key])) }}">
                                                    {{ $column->name }}
                                                </a>
                                            </th>
                                        @endif
                                    @else
                                        <th scope="col" class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
                                            {{ $column->name }}
                                        </th>
                                    @endif
                                @endif
                            @endforeach
                            <th></th>
                        </tr>
                        <!-- end headers -->
                        <!-- filters -->
                        <tr>
                            @include('leantony::grid.filter', ['columns' => $columns, 'formId' => $grid->getFilterFormId()])
                        </tr>
                        <!-- end filters -->
                        </thead>
                        <!-- data -->
                        <tbody>
                        @if($grid->hasItems())
                            @if($grid->warnIfEmpty())
                                <div class="alert alert-warning" role="alert">
                                    <strong><i class="fa fa-exclamation-triangle"></i>&nbsp;No data present!.</strong>
                                </div>
                            @endif
                        @else
                            @foreach($grid->getData() as $item)
                                @if($grid->allowsLinkableRows())
                                    @php
                                        $callback = call_user_func($grid->getLinkableCallback(), $grid->transformName(), $item);
                                    @endphp
                                    @php
                                        $trClassCallback = call_user_func($grid->getRowCssStyle(), $grid->transformName(), $item);
                                    @endphp
                                    <tr class="{{ trim("linkable " . $trClassCallback) }}" data-url="{{ $callback }}">
                                @else
                                    @php
                                        $trClassCallback = call_user_func($grid->getRowCssStyle(), $grid->transformName(), $item);
                                    @endphp
                                    <tr class="{{ $trClassCallback }}">
                                        @endif
                                        @foreach($columns as $column)
                                            @if(is_callable($column->data))
                                                @if($column->raw)
                                                    <td class="{{ $column->rowClass }}">
                                                        {!! call_user_func($column->data, $item, $column->key) !!}
                                                    </td>
                                                @else
                                                    <td class="{{ $column->rowClass }}">
                                                        {{ call_user_func($column->data , $item, $column->key) }}
                                                    </td>
                                                @endif
                                            @else
                                                @if($column->raw)
                                                    <td class="{{ $column->rowClass }}">
                                                        {!! $item->{$column->key} !!}
                                                    </td>
                                                @else
                                                    <td class="{{ $column->rowClass }}">
                                                        {{ $item->{$column->key} }}
                                                    </td>
                                                @endif
                                            @endif
                                            @if($loop->last && $grid->hasButtons('rows'))
                                                <td>
                                                    <div class="pull-right">
                                                        @foreach($grid->getButtons('rows') as $button)
                                                            @if(call_user_func($button->renderIf, $grid->transformName(), $item))
                                                                {!! $button->render(['gridName' => $grid->transformName(), 'gridItem' => $item]) !!}
                                                            @else
                                                                @continue
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                    @endforeach
                                @endif
                        </tbody>
                        <!-- end data -->
                    </table>
                </div>
                <!-- end grid contents -->
            </div>
            <div class="card-footer">
                <!-- pagination -->
                @if($grid->wantsPagination())
                    <div class="pull-right">
                        {{ $grid->getData()->appends(request()->query())->links($grid->getPaginationView(), ['pjaxTarget' => $grid->getId()]) }}
                    </div><!-- /.center -->
            @endif
            <!-- end pagination -->
            </div>
        </div>
    </div>
</div>
@push('grid_js')
    <script>
        (function ($) {
            var grid = "{{ '#' . $grid->getId() }}";
            var filterForm = "{{ '#' . $grid->getFilterFormId() }}";
            var searchForm = "{{ '#' . $grid->getSearchFormId() }}";
            _grids.grid.init({
                id: grid,
                filterForm: filterForm,
                dateRangeSelector: '.date-range',
                searchForm: searchForm,
                pjax: {
                    pjaxOptions: {
                        scrollTo: false
                    },
                    // what to do after a PJAX request. Js plugins have to be re-intialized
                    afterPjax: function (e) {
                        _grids.init();
                    }
                }
            });
        })(jQuery);
    </script>
@endpush