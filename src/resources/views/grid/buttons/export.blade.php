<a href="{{ is_callable($url) ? $url() : $url }}" class="{{ $class }}" data-toggle="dropdown" title="{{ $title }}">
    <i class="fa {{ $icon }}"></i>&nbsp;{{ $name }}
</a>
<ul class="dropdown-menu" role="menu">
    @foreach($exportOptions as $k => $v)
        <li><a href="{{ $v['url'] }}" title="{{ $v['title'] }}">
                <i class="fa fa-{{ $v['icon'] }}"></i>&nbsp;{{ $k }}
            </a>
        </li>
    @endforeach
</ul>