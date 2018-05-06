<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Listeners;

use Illuminate\Http\Request;
use Leantony\Grid\GridInterface;
use Leantony\Grid\GridResources;

class GridPaginationHandler
{
    use GridResources;

    /**
     * Specify if data should be paginated
     *
     * @var bool
     */
    protected $shouldPaginate = true;

    /**
     * GridPaginator constructor.
     * @param GridInterface $grid
     * @param Request $request
     * @param $builder
     */
    public function __construct(GridInterface $grid, Request $request, $builder)
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->query = $builder;
    }

    /**
     * Paginate the filtered data
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator
     */
    public function paginate()
    {
        if ($this->getGrid()->gridNeedsSimplePagination()) {

            return $this->simplePaginate();
        }
        $pageSize = $this->getGrid()->getGridPaginationPageSize();

        return $this->getQuery()->paginate($pageSize);
    }

    /**
     * Simple paginate
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate()
    {
        $pageSize = $this->getGrid()->getGridPaginationPageSize();

        return $this->getQuery()->simplePaginate($pageSize);
    }
}