<div class="col-md-{{ $colSize }}">
    <form method="GET" id="{{ $id }}" action="{{ $action }}"
          @foreach($dataAttributes as $k => $v)
          data-{{ $k }}={{ $v }}
            @endforeach
    >
        <div id="custom-search-input">
            <div class="input-group col-md-12">
                <input type="text" class="form-control" name="{{ $name }}"
                       placeholder="{{ $placeholder }}" value="{{ request($name) }}" required="required"/>
                <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">
                            <i class="glyphicon glyphicon-search"></i>
                        </button>
                    </span>
            </div>
        </div>
    </form>
</div>