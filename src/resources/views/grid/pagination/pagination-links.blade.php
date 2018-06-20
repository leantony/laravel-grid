@if($grid->wantsPagination())
    <div class="pull-right">
        {{ $grid->getData()->appends(request()->query())->links($grid->getGridPaginationView(), ['pjaxTarget' => $grid->getId()]) }}
    </div>
@endif