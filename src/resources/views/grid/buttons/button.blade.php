<a href="{{ $link }}" data-toggle="tooltip"
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