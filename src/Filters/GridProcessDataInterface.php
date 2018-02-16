<?php

namespace Leantony\Grid\Filters;

interface GridProcessDataInterface
{
    /**
     * Filter the grid rows
     *
     * @return void
     */
    public function filterRows();

    /**
     * Search the rows
     *
     * @return void
     */
    public function searchRows();

    /**
     * The table name to be sorted
     *
     * @return \Closure
     */
    public function getSortTable();

    /**
     * Sort a query builder
     *
     * @return void
     */
    public function sort();

    /**
     * Execute all filters
     *
     * @return void
     */
    public function executeFilters();

    /**
     * Paginate the filtered/searched data
     *
     * @return void
     */
    public function paginate();
}