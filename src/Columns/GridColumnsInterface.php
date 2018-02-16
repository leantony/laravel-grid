<?php

namespace Leantony\Grid\Columns;

interface GridColumnsInterface
{
    /**
     * Return the columns to be displayed on the grid
     *
     * @return array
     */
    public function getColumns(): array;

    /**
     * Set the columns to be displayed
     *
     * @return void
     * @throws \Exception
     */
    public function setColumns();

    /**
     * Get the processed rows
     *
     * @return array
     */
    public function getProcessedColumns(): array;
}