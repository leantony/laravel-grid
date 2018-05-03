<!-- Modal Header -->
<div class="modal-header">
    <h4 class="modal-title">{{ ucwords($action . ' '. class_basename($model)) }}</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<!-- Modal body -->
<form accept-charset="UTF-8" action="{{ $route }}" id="modal_form"
      data-pjax-target="#{{ $pjaxContainer ?? 'add-a-pjax-container-here' }}" method="{{ $method ?? 'POST' }}">
    <div class="modal-body">
        {!! csrf_field() !!}
        <div class="form-group row">
            <label for="input_key" class="col-sm-2 col-form-label">Input name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="input_key" name="input_key" placeholder="Enter value">
            </div>
        </div>
    </div>

    <!-- Modal footer -->
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ 'Close' }}</button>
        <button type="submit" class="btn btn-success"><i class="fa fa-floppy-o"></i>&nbsp;{{ 'Save' }}</button>
    </div>
</form>