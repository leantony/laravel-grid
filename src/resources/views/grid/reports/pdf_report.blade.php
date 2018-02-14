<h3>{{ $title }}</h3>
<hr>
<table class="table table-bordered">
    <thead>
    <tr>
        @foreach($rows as $row)
            <th class="{{ $row->headerClass }}">
                {{ $row->name }}
            </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
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
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
