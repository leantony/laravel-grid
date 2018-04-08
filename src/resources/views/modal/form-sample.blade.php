@if(isset($method))
    {!! BootForm::openHorizontal(['sm' => [4, 8], 'lg' => [2, 10]])->action($route)->class('form-horizontal')->id('modal_form')->data($dataVars ?? [])->$method() !!}
@else
    {!! BootForm::openHorizontal(['sm' => [4, 8], 'lg' => [2, 10]])->action($route)->class('form-horizontal')->id('modal_form')->data($dataVars ?? [])->post() !!}
@endif
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">{{ ucwords($action . ' '. class_basename($model)) }}</h4>
</div>
<div class="modal-body">
    <div id="modal-notification"></div>
    @if(isset($data))
        {!! BootForm::bind($data) !!}
    @endif
    {!! BootForm::text('Name', 'name') !!}
    {!! BootForm::textArea('Description', 'description') !!}
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ 'Close' }}
    </button>
    <button type="submit" class="btn btn-success"><i class="fa fa-floppy-o"></i>&nbsp;{{ 'Save' }}</button>
</div>
{!! BootForm::close() !!}