@foreach($columns as $col)
    @if(!$col->filter)
        <th></th>
    @elseif(!$col->filter->enabled)
        <th></th>
    @else
        <th>
            {!! $col->filter->render(['titleSetOnColumn' => $col->filterTitle]) !!}
        </th>
    @endif
    @if($loop->last)
        <th class="{{ $grid->getGridFilterFieldColumnClass() }}">
            <div class="pull-right">
                <button type="submit"
                        class="btn btn-outline-primary grid-filter-button"
                        title="filter data"
                        form="{{ $formId }}">Filter&nbsp;<i class="fa fa-filter"></i>
                </button>
            </div>
        </th>
    @endif
@endforeach
