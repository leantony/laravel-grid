@foreach($rows as $row)
    @if(!$row->filter)
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
                        form="{{ $formId }}">Filter
                </button>
            </div>
        </th>
    @endif
@endforeach
