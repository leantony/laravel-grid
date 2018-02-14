@if($type === 'toolbar')

    @if(call_user_func($beforeRender) === true)
        <a href="{{ is_callable($url) ? $url() : $url }}" data-toggle="tooltip"
           data-title="{{ $title }}"
           class="{{ $class }}"
           @foreach($dataAttributes as $k => $v)
           data-{{ $k }}={{ $v }}
                @endforeach
        >
            @if($icon)
                <i class="fa {{ $icon }}"></i>
            @endif
            {{ $name }}
        </a>
    @endif
@else

    @if(call_user_func($beforeRender, $gridName, $gridItem) === true)
        <a href="{{ call_user_func($url, $gridName, $gridItem) }}" data-toggle="tooltip"
           data-title="{{ $title }}"
           class="{{ $class }}"
           @foreach($dataAttributes as $k => $v)
           data-{{ $k }}={{ $v }}
                @endforeach
        >
            @if($icon)
                <i class="fa {{ $icon }}"></i>
            @endif
            {{ $name }}
        </a>
    @endif
@endif
