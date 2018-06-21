<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

trait HasGridConfigurations
{
    /**
     * The toolbar size. 6 columns on the right and 6 on the left
     * Left holds the search bar, while the right part holds the buttons
     *
     * @var array
     */
    private $toolbarSize;

    /**
     * Skip/ignore these columns when filtering, when supposedly passed in the query parameters
     *
     * @var array
     */
    private $columnsToSkipOnFilter;

    /**
     * css class for the grid
     *
     * @var string
     */
    private $gridClass;

    /**
     * @var string
     */
    private $gridHeaderClass;

    /**
     * @var string
     */
    private $gridView;

    /**
     * @var string
     */
    private $sortParam;

    /**
     * @var string
     */
    private $sortDirParam;

    /**
     * @var array
     */
    private $sortDirections;

    /**
     * @var string
     */
    private $searchParam;

    /**
     * @var string
     */
    private $searchType;

    /**
     * @var string
     */
    private $searchView;

    /**
     * @var string
     */
    private $filterType;

    /**
     * Export param option
     *
     * @var string
     */
    private $exportParam;

    /**
     * @var string
     */
    private $exportView;

    /**
     * Allowed document exports
     *
     * @var array
     */
    private $allowedExportTypes;

    /**
     * Max export rows. More = slower export process
     *
     * @var int
     */
    private $gridExportQueryChunkSize;

    /**
     * @var string
     */
    private $labelNamePattern;

    /**
     * @var boolean
     */
    private $shouldWarnIfEmpty;

    /**
     * @var string
     */
    private $paginationView;

    /**
     * @var string
     */
    private $paginationType;

    /**
     * @var int
     */
    private $paginationSize;

    /**
     * @var string
     */
    private $filterFieldColumnClass;

    /**
     * @var array
     */
    private $columnsToSkipOnGeneratingGrid;

    /**
     * @var string
     */
    private $gridNamespace;

    /**
     * @var array
     */
    private $defaultColumnDataOptions;

    /**
     * @var boolean
     */
    private $strictColumnExporting;

    /**
     * @var string
     */
    private $gridFooterClass;

    /**
     * @var string
     */
    private $gridTemplateView;

    public function getGridTemplateView(): string
    {
        if ($this->gridTemplateView === null) {
            $this->gridTemplateView = config('grid.templates.view', 'leantony::grid.templates.bs4-card');
        }
        return $this->gridTemplateView;
    }

    public function getGridFooterClass(): string
    {
        if ($this->gridFooterClass === null) {
            $this->gridFooterClass = config('grid.footer.class', 'table-info');
        }
        return $this->gridFooterClass;
    }

    public function getGridFilterFieldColumnClass(): string
    {
        if ($this->filterFieldColumnClass === null) {
            $this->filterFieldColumnClass = config('grid.columns.filter_field_class', 'grid-w-15');
        }
        return $this->filterFieldColumnClass;
    }

    public function getGridView(): string
    {
        if ($this->gridView === null) {
            $this->gridView = config('grid.view', 'leantony::grid.grid');
        }
        return $this->gridView;
    }

    public function getGridSortParam(): string
    {
        if ($this->sortParam === null) {
            $this->sortParam = config('grid.sort.param', 'sort_by');
        }
        return $this->sortParam;
    }

    public function getGridSortDirections(): array
    {
        if ($this->sortDirections === null) {
            $this->sortDirections = config('grid.sort.valid_directions', ['asc', 'desc']);
        }
        return $this->sortDirections;
    }


    public function getGridLabelNamePattern(): string
    {
        if ($this->labelNamePattern === null) {
            $this->labelNamePattern = config('grid.columns.label_pattern', "/[^a-z0-9 -]+/");
        }
        return $this->labelNamePattern;
    }

    public function getGridToolbarSize(): array
    {
        if ($this->toolbarSize === null) {
            $this->toolbarSize = config('grid.toolbar_size', [6, 6]);
        }
        return $this->toolbarSize;
    }

    public function gridShouldWarnIfEmpty(): bool
    {
        if ($this->shouldWarnIfEmpty === null) {
            $this->shouldWarnIfEmpty = config('grid.warn_when_empty', true);
        }
        return $this->shouldWarnIfEmpty;
    }

    public function getGridDefaultClass(): string
    {
        if ($this->gridClass === null) {
            $this->gridClass = config('grid.default_class', 'table table-bordered table-hover');
        }
        return $this->gridClass;
    }

    public function getGridDefaultHeaderClass(): string
    {
        if ($this->gridHeaderClass === null) {
            $this->gridHeaderClass = config('grid.default_header_class', "");
        }
        return $this->gridHeaderClass;
    }

    public function getGridColumnsToSkipOnFilter(): array
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
    public function getGridSortDirParam(): string
    {
        if ($this->sortDirParam === null) {
            $this->sortDirParam = config('grid.sort.dir_param', 'sort_dir');
        }
        return $this->sortDirParam;
    }

    public function getGridExportParam(): string
    {
        if ($this->exportParam === null) {
            $this->exportParam = config('grid.export.param', 'export');
        }
        return $this->exportParam;
    }

    public function getGridStrictExportStatus(): bool
    {
        if ($this->strictColumnExporting === null) {
            $this->strictColumnExporting = config('grid.export.strict_mode', true);
        }
        return $this->strictColumnExporting;
    }

    public function getGridPaginationView(): string
    {
        if ($this->paginationView === null) {
            $this->paginationView = !$this->gridNeedsSimplePagination()
                ? config('grid.pagination.default', 'leantony::grid.pagination.default')
                : config('grid.pagination.simple', 'leantony::grid.pagination.simple');
        }
        return $this->paginationView;
    }

    public function gridNeedsSimplePagination(): bool
    {
        return $this->getGridPaginationFunction() === 'simple';
    }

    public function getGridPaginationFunction(): string
    {
        if ($this->paginationType === null) {
            $this->paginationType = config('grid.pagination.type', 'default');
        }
        return $this->paginationType;
    }

    public function getGridPaginationPageSize(): int
    {
        if ($this->paginationSize === null) {
            $this->paginationSize = config('grid.pagination.default_size', 15);
        }
        return $this->paginationSize;
    }

    public function getGridSearchParam(): string
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
    public function getGridSearchView(): string
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
            $this->allowedExportTypes = config('grid.export.allowed_types', ['xlsx', 'csv']);
        }
        return $this->allowedExportTypes;
    }

    public function getGridExportView(): string
    {
        if ($this->exportView === null) {
            $this->exportView = config('grid.export.view', 'leantony::reports.report');
        }
        return $this->exportView;
    }

    public function getGridExportQueryChunkSize(): int
    {
        if ($this->gridExportQueryChunkSize === null) {
            $this->gridExportQueryChunkSize = config('grid.export.chunk_size', 300);
        }
        return $this->gridExportQueryChunkSize;
    }

    public function getGridColumnsToSkipOnGeneration(): array
    {
        if ($this->columnsToSkipOnGeneratingGrid === null) {
            $this->columnsToSkipOnGeneratingGrid = config('grid.generation.columns_to_skip', [
                'password',
                'password_hash',
            ]);
        }
        return $this->columnsToSkipOnGeneratingGrid;
    }

    public function getGridNamespace(): string
    {
        if ($this->gridNamespace === null) {
            $this->gridNamespace = config('grid.generation.namespace', "App\\Grids");
        }
        return $this->gridNamespace;
    }

    public function getGridDefaultColumnDataOptions(): array
    {
        if ($this->defaultColumnDataOptions === null) {
            $this->defaultColumnDataOptions = config('grid.columns.default_data_value', [
                "search" => ["enabled" => false],
                "filter" => ["enabled" => true, "operator" => "="]
            ]);
        }
        return $this->defaultColumnDataOptions;
    }
}