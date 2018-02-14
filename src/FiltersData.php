<?php

namespace Leantony\Grid;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait FiltersData
{
    use ExportsData;

    /**
     * Specify if data should be paginated
     *
     * @var bool
     */
    protected $shouldPaginate = true;

    /**
     * A query builder instance
     *
     * @var Builder
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
     * Functions to be called after filtering is done
     *
     * @var array
     */
    protected $afterFilterFunctions = ['export'];

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

            $this->executeSearches();

        } else {
            // otherwise do filter
            foreach ($this->filterFunctions as $filter) {
                if (method_exists($this, $filter)) {
                    $this->{$filter}();
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
     * Execute search
     *
     * @return void
     */
    public function executeSearches()
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
     * Export the data
     *
     * @return void
     */
    public function export()
    {
        if ($this->request->has($this->exportParam) && $this->allowsExporting) {

            $param = $this->request->get($this->exportParam);

            if (in_array($param, $this->allowedExportTypes)) {
                $this->exportExcel()->downloadExportedAs($param);
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

        $this->filteredData = $this->query->paginate($pageSize);
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
            $rows = $this->getRows();

            foreach ($rows as $k => $v) {
                // check searchable
                $canSearch = isset($v['searchable']) && $v['searchable'] === true;
                if (!$canSearch) {
                    continue;
                }
                // operator
                $operator = $v['searchOperator'] ?? 'like';

                $value = $this->request->get($this->searchParam);
                // skip empty requests
                if ($value === null || empty(trim($value))) {
                    continue;
                }

                // try using a custom search function if defined
                if (isset($v['searchCustom']) && is_callable($v['searchCustom'])) {
                    call_user_func($v['searchCustom'], $this->query, $k, $value);

                } else if (isset($v['filterCustom']) && is_callable($v['filterCustom'])) {
                    // otherwise, use the filter, if defined
                    call_user_func($v['filterCustom'], $this->query, $k, $value);
                } else {
                    if ($operator == 'like') {
                        $value = '%' . $value . '%';
                    }
                    $this->query->where($k, $operator, $value, $this->searchType);
                }
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
            $rows = $this->getRows();
            $tableRows = $this->getTableColumns();

            foreach ($rows as $k => $v) {
                // skip rows that are not to be filtered
                if (!isset($v['filter'])) {
                    continue;
                } else {

                    $operator = $v['filterOperator'] ?? '=';
                    $row = $k;
                    // value to be used during filtering
                    $value = $this->request->get($row);
                    // skip empty requests
                    if ($value === null || empty(trim($value))) {
                        continue;
                    }
                    // skip rows that are not allowed/not in table
                    if (!in_array($row, $tableRows)) {
                        continue;
                    } else {
                        // check for custom filter strategies and call them
                        if (isset($v['filterCustom']) && is_callable($v['filterCustom'])) {
                            call_user_func($v['filterCustom'], $this->query, $row, $value);
                        } else {
                            if ($operator == 'like') {
                                $value = '%' . $value . '%';
                            }
                            if (isset($v['date']) && $v['date']) {
                                // skip invalid dates
                                if (!strtotime($value)) {
                                    continue;
                                }
                                $this->query->whereDate($row, $operator, $value, $this->filterType);
                            } else {
                                $this->query->where($row, $operator, $value, $this->filterType);
                            }
                        }
                    }
                }
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
        $this->filteredData = $this->query->simplePaginate($pageSize);
    }

    /**
     * Sort a query builder
     *
     * @return void
     */
    public function sort()
    {
        if ($sort = $this->checkAndReturnSortParam()) {
            $this->query->orderBy($sort, $this->getSortDirection());
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
     * @return Builder
     */
    public function getQuery(): Builder
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