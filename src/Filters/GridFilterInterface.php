<?php

namespace Leantony\Grid;

use Leantony\Grid\Filters\GenericFilter;

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