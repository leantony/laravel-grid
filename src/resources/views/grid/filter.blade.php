@foreach($columns as $row)
    @if(!$row->filter)
        <th></th>
    @elseif(!$row->filter->enabled)
        <th></th>
    @else
        <th>
            {!! $row->filter !!}
        </th>
    @endif
    @if($loop->last)
        <th>
            <div class="pull-right">
                <button type="submit"
                        class="btn btn-default"
                        title="filter data"
                        form="{{ $formId }}">Filter&nbsp;<i class="fa fa-filter"></i>
                </button>
            </div>
        </th>
    @endif
@endforeach
