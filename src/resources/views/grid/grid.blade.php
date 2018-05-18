<div class="row laravel-grid" id="{{ $grid->getId() }}">
    <div class="col-md-12 col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="pull-left">
                    <h4 class="grid-title">{{ $grid->getName() }}</h4>
                </div>
                <!-- pagination info -->
            @include('leantony::grid.pagination.pagination-info', ['grid' => $grid, 'direction' => 'right'])
            <!-- end pagination info -->
            </div>
            <div class="card-body">
                <!-- search form -->
                <div class="row">
                {!! $grid->renderSearchForm() !!}
                <!-- toolbar buttons -->
                    @if($grid->hasButtons('toolbar'))
                        <div class="col-md-{{ $grid->getGridToolbarSize()[1] }}">
                            <div class="pull-right">
                                @foreach($grid->getButtons('toolbar') as $button)
                                    {!! $button->render() !!}
                                @endforeach
                            </div>
                        </div>
                @endif
                <!-- end toolbar buttons -->
                </div>
                <!-- end search form -->
                <!-- filter form declaration -->
                <form action="{{ $grid->getSearchUrl() }}" method="GET" id="{{ $grid->getFilterFormId() }}"></form>
                <!-- grid contents -->
                <div class="table-responsive grid-wrapper">
                    <table class="{{ $grid->getClass() }}">
                        <thead>
                        <!-- headers -->
                        <tr>
                            @foreach($columns as $column)

                                @if($loop->first)

                                    @if($column->sortable)
                                        <th scope="col"
                                            class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}"
                                            title="click to sort by {{ $column->key }}">
                                            <a data-trigger-pjax="1" class="data-sort"
                                               href="{{ $grid->getSortUrl($column->key, $grid->getSelectedSortDirection()) }}">
                                                {{ $column->name }}
                                            </a>
                                        </th>
                                    @else
                                        <th class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
                                            {{ $column->name }}
                                        </th>
                                    @endif
                                @else
                                    @if($column->sortable)
                                        <th scope="col" title="click to sort by {{ $column->key }}"
                                            class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
                                            <a data-trigger-pjax="1" class="data-sort"
                                               href="{{ $grid->getSortUrl($column->key, $grid->getSelectedSortDirection()) }}">
                                                {{ $column->name }}
                                            </a>
                                        </th>
                                    @else
                                        <th scope="col"
                                            class="{{ is_callable($column->columnClass) ? call_user_func($column->columnClass) : $column->columnClass }}">
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
            @include('leantony::grid.pagination.pagination-info', ['grid' => $grid, 'direction' => 'left', 'atFooter' => true])
            <!-- pagination -->
                @if($grid->wantsPagination())
                    <div class="pull-right">
                        {{ $grid->getData()->appends(request()->query())->links($grid->getGridPaginationView(), ['pjaxTarget' => $grid->getId()]) }}
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