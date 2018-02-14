<select name="{{ $name }}" id="{{ $id }}" form="{{ $formId }}" class="{{ $class }}" style="font-size: 12px;">
    <option value=""></option>
    @foreach($data as $k => $v)
        @if(request($name) !== null && request($name) == $k)
            <option value="{{ $k }}" selected>{{ $v }}</option>
        @else
            <option value="{{ $k }}">{{ $v }}</option>
        @endif
    @endforeach
</select>