@foreach($columns as $row)
    @if(!$row->filter)
        <th></th>
    @elseif(!$row->filter->enabled)
        <th></th>
    @else
        <th class="">
            {!! $row->filter !!}
        </th>
    @endif
    @if($loop->last)
        <th class="{{ $grid->getFilterFieldColumnClass() }}">
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
