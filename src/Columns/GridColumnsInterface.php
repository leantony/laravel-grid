<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

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
     * Process the columns that were supplied
     *
     * @return array
     */
    public function processColumns();
}