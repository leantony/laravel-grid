<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Listeners;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Leantony\Grid\GridInterface;
use Leantony\Grid\GridResources;

class SortDataHandler
{
    use GridResources;

    /**
     * SortDataHandler constructor.
     * @param GridInterface $grid
     * @param Request $request
     * @param $builder
     * @param $validTableColumns
     */
    public function __construct(GridInterface $grid, Request $request, $builder, $validTableColumns, $args)
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->query = $builder;
        $this->validGridColumns = $validTableColumns;
        $this->args = $args;
    }

    /**
     * The table name to be sorted
     *
     * @return \Closure
     */
    public function getSortTable()
    {
        $gridName = $this->getGrid()->getName();

        return function () use ($gridName) {
            return Str::plural(Str::slug($gridName, '_'));
        };
    }

    /**
     * Sort a query builder
     *
     * @return void
     */
    public function sort()
    {
        if ($sort = $this->checkAndReturnSortParam()) {
            $this->getQuery()->orderBy($sort, $this->getSortDirection());

            if (isset($this->getArgs()['urlUpdater'])) {
                call_user_func($this->getArgs()['sortUrlUpdater'], $sort, $this->getSortDirection() === 'asc' ? 'desc' : 'asc');
            }
        }

    }

    /**
     * Check and return sort parameter
     *
     * @return string|false
     */
    public function checkAndReturnSortParam()
    {
        if ($this->getRequest()->has($this->getGrid()->getGridSortParam())) {
            $value = $this->request->get($this->getGrid()->getGridSortParam());

            if (in_array($value, $this->getValidGridColumns())) {
                return $value;
            }
        }
        return false;
    }

    /**
     * The sort direction
     *
     * @return string
     */
    public function getSortDirection()
    {
        if ($dir = $this->getRequest()->has($this->getGrid()->getGridSortDirParam())) {
            if (in_array($dir, $this->getGrid()->getGridSortDirections())) {
                return $dir;
            }
        }
        // default to the first sort option
        return $this->getGrid()->getGridSortDirections()[0];
    }
}