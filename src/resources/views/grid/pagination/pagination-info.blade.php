@if($grid->wantsPagination() && !$grid->gridNeedsSimplePagination())
    <div class="pull-{{ $direction }}">
        <b>
            @if($grid->getData()->total() <= $grid->getData()->perpage())
                @if(!isset($atFooter))
                    Showing {{ ($grid->getData()->currentpage() - 1 ) * $grid->getData()->perpage() + 1 }}
                    to {{ $grid->getData()->total() }}
                    of {{ $grid->getData()->total() }} entries.
                @endif
            @else
                Showing {{ ($grid->getData()->currentpage() - 1 ) * $grid->getData()->perpage() + 1 }}
                to {{ $grid->getData()->currentpage() * $grid->getData()->perpage() }}
                of {{ $grid->getData()->total() }} entries.
            @endif
        </b>
    </div>
@else
    @if(isset($atFooter))
        @if($grid->getData()->count() >= $grid->getData()->perpage())
            <div class="pull-{{ $direction }}">
                <b>
                    Showing {{ $grid->getData()->count() }} records for this page.
                </b>
            </div>
        @endif
    @else
        <div class="pull-{{ $direction }}">
            <b>
                Showing {{ $grid->getData()->count() }} records for this page.
            </b>
        </div>
    @endif
@endif