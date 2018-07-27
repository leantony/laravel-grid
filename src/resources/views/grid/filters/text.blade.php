<input type="text" name="{{ $name }}" id="{{ $id }}"
       form="{{ $formId }}"
       class="{{ $class }}" value="{{ request($name) }}" title="{{ $titleSetOnColumn ?? $title }}" placeholder="{{ $titleSetOnColumn ?? $title }}"
       @foreach($dataAttributes as $k => $v)
       data-{{ $k }}={{ $v }}
        @endforeach
>