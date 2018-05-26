@if(call_user_func($renderIf) === true)
    <div class="btn-group pull-right grid-export-button" role="group" title="{{ $title }}">
        <button id="export-button" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
            <i class="fa {{ $icon }}"></i>&nbsp;{{ $name }}
        </button>
        <div class="dropdown-menu" aria-labelledby="export-button">
            @foreach($exportOptions as $k => $v)
                <a href="{{ $v['url'] }}" class="dropdown-item" title="{{ $v['title'] }}" aria-labelledby="{{ $k }}">
                    <i class="fa fa-{{ $v['icon'] }}"></i>&nbsp;{{ $v['name'] }}
                </a>
            @endforeach
        </div>
    </div>
@endif