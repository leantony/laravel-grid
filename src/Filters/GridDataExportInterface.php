<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GridDataExportInterface extends GridProcessDataInterface
{
    /**
     * Get the data to be exported
     *
     * @return Collection|array|LengthAwarePaginator
     */
    public function getExportData();

    /**
     * Gets the columns to be exported
     *
     * @return array
     */
    public function getColumnsToExport();
}