<?php

namespace Leantony\Grid\Filters;

interface GridFilterInterface
{
    /**
     * Add a filter to the row
     *
     * @param $rowValue
     * @param $rowKey
     * @return GenericFilter
     */
    public function pushFilter($rowValue, $rowKey): GenericFilter;
}