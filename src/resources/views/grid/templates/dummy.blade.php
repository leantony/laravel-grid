<div class="row laravel-grid" id="{{ $grid->getId() }}">
    <div class="col-md-12 col-xs-12 col-sm-12">
        <h2>{{ $grid->renderTitle() }}</h2>
        <p>dummy</p>
        @yield('data')
        {!! $grid->renderPaginationLinksSection() !!}
    </div>
</div>