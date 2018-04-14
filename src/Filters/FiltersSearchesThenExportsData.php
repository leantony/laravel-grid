<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait FiltersSearchesThenExportsData
{
    use ExportsData,
        SearchesData,
        FiltersData;

    /**
     * Specify if data should be paginated
     *
     * @var bool
     */
    protected $shouldPaginate = true;

    /**
     * A query builder instance
     *
     * @var \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * The request instance
     *
     * @var Request
     */
    protected $request;

    /**
     * The filter type. AND, OR, NOT, etc
     *
     * @var string
     */
    protected $filterType = 'and';

    /**
     * The search type. AND, OR, NOT, etc
     *
     * @var string
     */
    protected $searchType = 'or';

    /**
     * Columns to be used during row processing, to find
     * the search form placeholder
     *
     * @var array
     */
    protected $searchColumns = [];

    /**
     * Sort directions
     *
     * @var array
     */
    protected $valid_directions = ['asc', 'desc'];

    /**
     * The table to be sorted
     *
     * @var string
     */
    protected $sortTable = null;

    /**
     * Sort column name
     *
     * @var string
     */
    protected $sortParam = 'sort_by';

    /**
     * @var string
     */
    protected $searchParam = 'q';

    /**
     * Sort direction
     *
     * @var string
     */
    protected $sortDirParam = 'sort_dir';

    /**
     * Export param option
     *
     * @var string
     */
    protected $exportParam = 'export';

    /**
     * Allowed document exports
     *
     * @var array
     */
    protected $allowedExportTypes = ['pdf', 'xlsx', 'xls', 'csv'];

    /**
     * Skip/ignore these columns when filtering, when supposedly passed in the query parameters
     *
     * @var array
     */
    protected $columnsToSkipOnFilter = [
        'password',
        'remember_token',
        'activation_code'
    ];

    /**
     * Table columns for the grid to be sorted
     *
     * @var array
     */
    protected $tableColumns = [];

    /**
     * Functions to be called before filtering is done
     *
     * @var array
     */
    protected $beforeFilterFunctions = ['checkForFilters', 'restrictQueryResults'];

    /**
     * Filter functions to be called by default. At least call them in order
     *
     * @var array
     */
    protected $filterFunctions = ['filterRows', 'sort', 'paginate'];

    /**
     * Filter functions to be called by default. At least call them in order
     *
     * @var array
     */
    protected $searchFunctions = ['searchRows', 'sort', 'paginate'];

    /**
     * The filtered data. Update this after all filters have been executed. E.g during pagination
     *
     * @var Collection|LengthAwarePaginator
     */
    private $filteredData;

    /**
     * Execute all filters
     *
     * @return void
     */
    public function executeFilters()
    {
        if (!empty($this->beforeFilterFunctions)) {
            foreach ($this->beforeFilterFunctions as $item) {
                if (method_exists($this, $item)) {
                    $result = $this->{$item}();
                    if (!$result) {
                        break;
                    }
                }
            }
        }
        // route searches to search functions
        if ($this->request->has($this->getSearchParam())) {

            $this->executeSearchFunctions();

        } else {
            // otherwise do filter
            $this->executeFilterFunctions();
        }
    }

    /**
     * Get the search param name
     *
     * @return string
     */
    public function getSearchParam(): string
    {
        return $this->searchParam;
    }

    /**
     * Execute search functions
     *
     * @return void
     */
    public function executeSearchFunctions()
    {
        foreach ($this->searchFunctions as $searchFunction) {
            if (method_exists($this, $searchFunction)) {
                $this->{$searchFunction}();
            }
        }
        if (!empty($this->afterFilterFunctions)) {
            foreach ($this->afterFilterFunctions as $item) {
                if (method_exists($this, $item)) {
                    $this->{$item}();
                }
            }
        }
    }

    /**
     * Execute filter functions
     *
     * @return void
     */
    public function executeFilterFunctions()
    {
        foreach ($this->filterFunctions as $filter) {
            if (method_exists($this, $filter)) {
                $this->{$filter}();
            }
        }
    }

    /**
     * Export the data
     *
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function export()
    {
        if ($this->wantsToExport()) {

            $param = $this->request->get($this->exportParam);

            if (in_array($param, $this->allowedExportTypes)) {
                return $this->exportAs($param);
            }
        }
    }

    /**
     * Paginate the filtered data
     *
     * @return void
     */
    public function paginate()
    {
        $pageSize = $this->getPageSize();

        $this->filteredData = $this->getQuery()->paginate($pageSize);
    }

    /**
     * Get the page size
     *
     * @return int
     */
    protected function getPageSize(): int
    {
        return config('grids.pagination_limit');
    }

    /**
     * Search the rows
     *
     * @return void
     */
    public function searchRows()
    {
        if (!empty($this->request->query())) {
            $columns = $this->getColumns();

            foreach ($columns as $columnName => $columnData) {
                // check searchable
                if (!$this->canSearchColumn($columnName, $columnData)) {
                    continue;
                }
                // check user input
                if (!$this->canUseProvidedUserInput($this->request->get($this->searchParam))) {
                    continue;
                }
                // operator
                $operator = $this->fetchSearchOperator($columnName, $columnData)['operator'];

                $this->doSearch($columnName, $columnData, $operator, $this->request->get($this->searchParam));
            }
        }
    }

    /**
     * Filter the grid rows
     *
     * @return void
     */
    public function filterRows()
    {
        if (!empty($this->request->query())) {
            $columns = $this->getColumns();
            $tableColumns = $this->getTableColumns();

            foreach ($columns as $columnName => $columnData) {
                // skip rows that are not to be filtered
                if (!$this->canFilter($columnName, $columnData)) {
                    continue;
                }
                // user input check
                if (!$this->canUseProvidedUserInput($this->request->get($columnName))) {
                    continue;
                }
                // column check. Since the column data is coming from a user query
                if (!$this->canUseProvidedColumn($columnName, $tableColumns)) {
                    continue;
                }
                $operator = $this->extractFilterOperator($columnName, $columnData)['operator'];

                $this->doFilter($columnName, $columnData, $operator, $this->request->get($columnName));
            }
        }
    }

    /**
     * Get valid columns in the table
     *
     * @return array
     */
    public function getTableColumns()
    {
        if (empty($this->tableColumns)) {
            $cols = Schema::getColumnListing(call_user_func($this->getSortTable()));
            $rejects = $this->columnsToSkipOnFilter;
            $this->tableColumns = collect($cols)->reject(function ($v) use ($rejects) {
                return in_array($v, $rejects);
            })->toArray();
        }
        return $this->tableColumns;
    }

    /**
     * The table name to be sorted
     *
     * @return \Closure
     */
    public function getSortTable()
    {
        $gridName = $this->name;

        return function () use ($gridName) {
            return Str::plural(Str::slug($gridName, '_'));
        };
    }

    /**
     * Simple paginate
     *
     * @return void
     */
    public function simplePaginate()
    {
        $pageSize = $this->getPageSize();
        $this->filteredData = $this->getQuery()->simplePaginate($pageSize);
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
        }
    }

    /**
     * Check and return sort parameter
     *
     * @return string|false
     */
    public function checkAndReturnSortParam()
    {
        if ($this->request->has($this->sortParam)) {
            $value = $this->request->get($this->sortParam);

            if (in_array($value, $this->getTableColumns())) {
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
        if ($dir = $this->request->has($this->sortDirParam)) {
            if (in_array($dir, $this->valid_directions)) {
                return $dir;
            }
        }
        return $this->valid_directions[0];
    }

    /**
     * Get the query builder
     *
     * @return \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the http request instance
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getSortParam(): string
    {
        return $this->sortParam;
    }

    /**
     * @return string
     */
    public function getSortDirParam(): string
    {
        return $this->sortDirParam;
    }

    /**
     * Get the filtered data
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredData()
    {
        return $this->filteredData;
    }

    /**
     * Check if any filters are present
     *
     * @throws \Exception
     */
    protected function checkForFilters()
    {
        if (empty($this->filterFunctions)) {
            throw new \Exception("Make sure at least one filter function is called. Just 'sort' is enough");
        }
    }
}