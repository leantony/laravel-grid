<div class="row laravel-grid" id="{{ $grid->getId() }}">
    <div class="col-md-12 col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="pull-left">
                    <h4 class="grid-title">{{ $grid->renderTitle() }}</h4>
                </div>
                {!! $grid->renderPaginationInfoAtHeader() !!}
            </div>
            <div class="card-body">
                @yield('data')
            </div>
            <div class="card-footer">
                {!! $grid->renderPaginationInfoAtFooter() !!}
                {!! $grid->renderPaginationLinksSection() !!}
            </div>
        </div>
    </div>
</div>