<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

use Illuminate\Pagination\LengthAwarePaginator;

trait ConfiguresGrid
{
    protected function getGridView(): string
    {
        return config('grid.view', 'leantony::grid.grid');
    }

    public function getSortParam(): string
    {
        return config('grid.sort.param', 'sort_by');
    }

    public function getSortValidDirections(): array
    {
        return config('grid.sort.valid_directions', ['asc', 'desc']);
    }

    public function getLabelNamePattern(): string
    {
        return config('grid.columns.label_pattern', "/[^a-z0-9 -]+/");
    }

    public function getToolbarSize(): array
    {
        return config('grid.toolbar_size', [6, 6]);
    }

    public function shouldWarnIfEmpty(): bool
    {
        return config('grid.warn_when_empty', true);
    }

    public function getGridDefaultClass(): string
    {
        return config('grid.default_class', 'table table-bordered table-hover');
    }

    public function getColumnsToSkipOnFilter(): array
    {
        return config('grid.filter.columns_to_skip', [
            'password',
            'remember_token',
            'activation_code'
        ]);
    }

    /**
     * @return string
     */
    public function getSortDirParam(): string
    {
        return config('grid.sort.dir_param', 'sort_dir');
    }

    public function getExportParam(): string
    {
        return config('grid.export.param', 'export');
    }

    public function getPaginationView()
    {
        return $this->data instanceof LengthAwarePaginator
            ? config('grid.pagination.default', 'leantony::grid.pagination.default')
            : config('grid.pagination.simple', 'leantony::grid.pagination.simple');
    }

    public function getSearchParam()
    {
        return config('grid.search.param', 'q');
    }

    /**
     * Return the view used to display the search form
     *
     * @return string
     */
    public function getSearchView(): string
    {
        return config('grid.search.view', 'leantony::grid.search');
    }
}