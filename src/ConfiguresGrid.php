<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

trait ConfiguresGrid
{
    /**
     * The toolbar size. 6 columns on the right and 6 on the left
     * Left holds the search bar, while the right part holds the buttons
     *
     * @var array
     */
    protected $toolbarSize;

    /**
     * Skip/ignore these columns when filtering, when supposedly passed in the query parameters
     *
     * @var array
     */
    protected $columnsToSkipOnFilter;

    /**
     * css class for the grid
     *
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $gridView;

    /**
     * @var string
     */
    protected $sortParam;

    /**
     * @var string
     */
    protected $sortDirParam;

    /**
     * @var array
     */
    protected $sortDirections;

    /**
     * @var string
     */
    protected $searchParam;

    /**
     * @var string
     */
    protected $searchType;

    /**
     * @var string
     */
    protected $searchView;

    /**
     * @var string
     */
    protected $filterType;

    /**
     * Export param option
     *
     * @var string
     */
    protected $exportParam;

    /**
     * Allowed document exports
     *
     * @var array
     */
    protected $allowedExportTypes;

    /**
     * Max export rows. More = slower export process
     *
     * @var int
     */
    protected $maxExportRows;

    /**
     * @var string
     */
    protected $labelNamePattern;

    /**
     * @var boolean
     */
    protected $shouldWarnIfEmpty;

    /**
     * @var string
     */
    protected $paginationView;

    /**
     * @var string
     */
    protected $paginationType;

    /**
     * @var int
     */
    protected $paginationSize;

    public function getGridView(): string
    {
        if ($this->gridView === null) {
            $this->gridView = config('grid.view', 'leantony::grid.grid');
        }
        return $this->gridView;
    }

    public function getSortParam(): string
    {
        if ($this->sortParam === null) {
            $this->sortParam = config('grid.sort.param', 'sort_by');
        }
        return $this->sortParam;
    }

    public function getSortDirections(): array
    {
        if ($this->sortDirections === null) {
            $this->sortDirections = config('grid.sort.valid_directions', ['asc', 'desc']);
        }
        return $this->sortDirections;
    }


    public function getLabelNamePattern(): string
    {
        if ($this->labelNamePattern === null) {
            $this->labelNamePattern = config('grid.columns.label_pattern', "/[^a-z0-9 -]+/");
        }
        return $this->labelNamePattern;
    }

    public function getToolbarSize(): array
    {
        if ($this->toolbarSize === null) {
            $this->toolbarSize = config('grid.toolbar_size', [6, 6]);
        }
        return $this->toolbarSize;
    }

    public function shouldWarnIfEmpty(): bool
    {
        if ($this->shouldWarnIfEmpty === null) {
            $this->shouldWarnIfEmpty = config('grid.warn_when_empty', true);
        }
        return $this->shouldWarnIfEmpty;
    }

    public function getGridDefaultClass(): string
    {
        if ($this->class === null) {
            $this->class = config('grid.default_class', 'table table-bordered table-hover');
        }
        return $this->class;
    }

    public function getColumnsToSkipOnFilter(): array
    {
        if ($this->columnsToSkipOnFilter === null) {
            $this->columnsToSkipOnFilter = config('grid.filter.columns_to_skip', [
                'password',
                'remember_token',
                'activation_code'
            ]);
        }
        return $this->columnsToSkipOnFilter;
    }

    /**
     * @return string
     */
    public function getSortDirParam(): string
    {
        if ($this->sortDirParam === null) {
            $this->sortDirParam = config('grid.sort.dir_param', 'sort_dir');
        }
        return $this->sortDirParam;
    }

    public function getExportParam(): string
    {
        if ($this->exportParam === null) {
            $this->exportParam = config('grid.export.param', 'export');
        }
        return $this->exportParam;
    }

    public function getPaginationView(): string
    {
        if ($this->paginationView === null) {
            $this->paginationView = $this->getPaginationFunction() === 'default'
                ? config('grid.pagination.default', 'leantony::grid.pagination.default')
                : config('grid.pagination.simple', 'leantony::grid.pagination.simple');
        }
        return $this->paginationView;
    }

    public function getPaginationPageSize(): int
    {
        if ($this->paginationSize === null) {
            $this->paginationSize = config('grids.pagination.default_size', 15);
        }
        return $this->paginationSize;
    }

    public function getPaginationFunction(): string
    {
        if ($this->paginationType === null) {
            $this->paginationType = config('grids.pagination.type', 'default');
        }
        return $this->paginationType;
    }

    public function getSearchParam(): string
    {
        if ($this->searchParam === null) {
            $this->searchParam = config('grid.search.param', 'q');
        }
        return $this->searchParam;
    }

    /**
     * Return the view used to display the search form
     *
     * @return string
     */
    public function getSearchView(): string
    {
        if ($this->searchView === null) {
            $this->searchView = config('grid.search.view', 'leantony::grid.search');
        }
        return $this->searchView;
    }

    public function getGridFilterQueryType(): string
    {
        if ($this->filterType === null) {
            $this->filterType = config('grid.filter.query_type', 'and');
        }
        return $this->filterType;
    }

    public function getGridSearchQueryType(): string
    {
        if ($this->searchType === null) {
            $this->searchType = config('grid.search.query_type', 'or');
        }
        return $this->searchType;
    }

    public function getGridExportTypes(): array
    {
        if ($this->allowedExportTypes === null) {
            $this->allowedExportTypes = config('grid.export.allowed_types', ['pdf', 'xlsx', 'xls', 'csv']);
        }
        return $this->allowedExportTypes;
    }

    public function getMaxRowsForExport(): int
    {
        if ($this->maxExportRows === null) {
            $this->maxExportRows = config('grid.export.max_rows', 5000);
        }
        return $this->maxExportRows;
    }
}