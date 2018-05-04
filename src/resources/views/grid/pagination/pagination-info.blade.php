@if($grid->wantsPagination() && !$grid->needsSimplePagination())
    <div class="pull-{{ $direction }}">
        <b>
            @if($grid->getData()->total() <= $grid->getData()->perpage())
                Showing {{ ($grid->getData()->currentpage() -1 ) * $grid->getData()->perpage() + 1 }}
                to {{ $grid->getData()->total() }}
                of {{ $grid->getData()->total() }} entries.
            @else
                Showing {{ ($grid->getData()->currentpage() -1 ) * $grid->getData()->perpage() + 1 }}
                to {{ $grid->getData()->currentpage() * $grid->getData()->perpage() }}
                of {{ $grid->getData()->total() }} entries.
            @endif
        </b>
    </div>
@else
    <div class="pull-{{ $direction }}">
        <b>
            Showing {{ $grid->getData()->count() }} records for this page.
        </b>
    </div>
@endif