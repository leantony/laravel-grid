<?php

namespace Leantony\Grid;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class Grid implements Htmlable, GridInterface, GridButtonsInterface, GridFilterInterface
{
    use FiltersSearchesThenExportsData,
        AddsColumnFilters,
        ConfiguresButtons,
        ConfiguresRoutes,
        GridColumns;

    /**
     * Specify if the grid allows exporting of records
     *
     * @var bool
     */
    protected $allowsExporting = true;

    /**
     * Specify if the rows on the table should be clicked to navigate to the record
     *
     * @var bool
     */
    protected $linkableRows = false;

    /**
     * css class for the grid
     *
     * @var string
     */
    protected $class = 'table table-bordered table-hover';

    /**
     * The id of the grid
     *
     * @var string
     */
    protected $id = 'grid-leantony';

    /**
     * The name of the grid
     *
     * @var string
     */
    protected $name = 'grid';

    /**
     * Display a warning message if there is no data
     *
     * @var bool
     */
    protected $warnIfEmpty = true;

    /**
     * Extra parameters sent to the grid from the view
     *
     * @var array
     */
    protected $extraParams = [];

    /**
     * Data that will be sent to the view
     *
     * @var LengthAwarePaginator|Collection|array
     */
    protected $data;

    /**
     * The rows that have been processed
     *
     * @var array
     */
    protected $processedColumns = [];

    /**
     * The toolbar size. 6 columns on the right and 6 on the left
     *
     * @var array
     */
    protected $toolbarSize = [6, 6];

    /**
     * Buttons for the grid
     *
     * @var array
     */
    protected $buttons = [];

    /**
     * Create the grid
     *
     * @param array $params
     * @return GridInterface
     */
    public function create(array $params): GridInterface
    {
        foreach ($params as $k => $v) {
            $this->__set($k, $v);
        }
        $this->init();
        return $this;
    }

    /**
     * Initialize grid variables
     *
     * @return void
     */
    public function init()
    {
        // the grid ID
        $this->id = Str::singular(Str::camel($this->name)) . '-' . 'grid';
        // any links defined
        $this->setRoutes();
        // default buttons on the grid
        $this->setButtons();
        // configuration to the buttons already set including adding new ones
        $this->configureButtons();
        // user defined columns
        $this->setColumns();
        // data filters
        $this->executeFilters();
        // get filtered data
        $this->data = $this->getFilteredData();
    }

    /**
     * Get the data
     *
     * @return Paginator|LengthAwarePaginator|Collection|array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Dynamically get an attribute
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new InvalidArgumentException("Property " . $name . " does not exit on this class");
    }

    /**
     * Dynamically set an attribute
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Render the grid
     *
     * @return string
     */
    public function render()
    {
        return view($this->getGridView(), $this->compactData(func_get_args()))->render();
    }

    /**
     * Get the view to be used when rendering the grid
     *
     * @return string
     */
    protected function getGridView(): string
    {
        return 'leantony::grid.grid';
    }

    /**
     * Specify the data to be sent to the view
     *
     * @param array $params
     * @return array
     */
    protected function compactData($params = [])
    {
        $data = [
            'grid' => $this,
            'columns' => $this->processColumns()
        ];
        return array_merge($data, $this->getExtraParams($params));
    }

    /**
     * Process the rows that were supplied
     *
     * @return array
     */
    public function processColumns()
    {
        if (!empty($this->processedColumns)) {
            return $this->processedColumns;
        }
        $columns = [];
        // process
        foreach ($this->columns as $columnName => $columnData) {

            // should render
            if (!$this->canRenderColumn($columnName, $columnData)) {
                continue;
            }

            // css styles
            $styles = $this->fetchCssStyles($columnName, $columnData);

            $columnClass = $styles['columnClass'];

            $rowClass = $styles['rowClass'];

            // label
            $label = $this->fetchColumnLabel($columnName, $columnData)['label'];

            // searchable columns
            $searchable = $this->fetchSearchableColumns($columnName, $columnData)['searchable'];

            // filter
            $filter = $this->fetchColumnFilter($columnName, $columnData)['filter'];

            // data
            $data = $this->fetchColumnData($columnName, $columnData)['data'];

            // once we are done, push to columns array
            array_push($columns, new Column([
                'name' => $label,
                'key' => $columnName,
                'data' => $data,
                'searchable' => $searchable,
                'rowClass' => $rowClass,
                'columnClass' => $columnClass,
                'sortable' => $columnData['sort'] ?? true,
                'filter' => $filter,
                'raw' => $columnData['raw'] ?? false,
                'export' => $columnData['export'] ?? true,
            ]));
        }

        $this->processedColumns = $columns;

        return $this->processedColumns;
    }

    /**
     * Any extra parameters that need to be passed to the grid
     * $params is func_get_args() passed from render
     *
     * @param array $params
     * @return array
     */
    public function getExtraParams($params)
    {
        return array_merge($this->extraParams, $params);
    }

    /**
     * Get id for the filter form
     *
     * @return string
     */
    public function getFilterFormId()
    {
        return $this->getId() . '-' . 'filter';
    }

    /**
     * Return the ID of the grid
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Override this method and return a callback so that linkable rows are applied
     *
     * @return Closure
     * @throws \InvalidArgumentException
     */
    public function getLinkableCallback(): Closure
    {
        if ($this->allowsLinkableRows()) {
            throw new InvalidArgumentException("Specify a callback that would return a link for every row of the table.");
        }
    }

    /**
     * Returns a closure that will be executed to apply a class for each row on the grid
     * The closure takes two arguments - `name` of grid, and `item` being iterated upon
     *
     * @return Closure
     */
    abstract public function getRowCssStyle(): Closure;

    /**
     * If the grid rows can be clicked on as links
     *
     * @return bool
     */
    public function allowsLinkableRows()
    {
        return $this->linkableRows;
    }

    /**
     * Render the search form on the grid
     *
     * @return string
     */
    public function getSearch()
    {
        $params = func_get_args();
        $data = [
            'colSize' => $this->toolbarSize[0], // size
            'action' => $this->getSearchRoute(),
            'id' => $this->getSearchFormId(),
            'name' => $this->getSearchParam(),
            'dataAttributes' => [],
            'placeholder' => $this->getSearchPlaceholder(),
        ];

        return view($this->getSearchView(), array_merge($data, $params))->render();
    }

    /**
     * Get the form id used for search
     *
     * @return string
     */
    public function getSearchFormId(): string
    {
        return 'search' . '-' . $this->getId();
    }

    /**
     * Get the placeholder to use on the search form
     *
     * @return string
     */
    private function getSearchPlaceholder()
    {
        if (empty($this->searchableColumns)) {
            $placeholder = Str::plural(Str::slug($this->getName()));

            return sprintf('search %s ...', $placeholder);
        }

        $placeholder = collect($this->searchableColumns)->implode(',');

        return sprintf('search %s by their %s ...', Str::lower($this->getName()), $placeholder);
    }

    /**
     * Get the name of the grid. Can be the table name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Transform the name of the grid, to a short, identifier
     * Useful for route param names
     *
     * @return string
     */
    public function transformName()
    {
        return Str::slug(Str::singular($this->getName()), '_');
    }

    /**
     * Check if grid has items
     *
     * @return bool
     */
    public function hasItems()
    {
        if ($this->wantsPagination()) {
            return $this->data->getCollection()->isEmpty();
        }
        return empty($this->data) || count($this->data) === 0;
    }

    /**
     * Check if the data needs to be paginated
     *
     * @return bool
     */
    public function wantsPagination()
    {
        // both simple and normal
        return $this->data instanceof LengthAwarePaginator || $this->data instanceof Paginator;
    }

    /**
     * Display a warning message if the grid has no data
     *
     * @return bool
     */
    public function warnIfEmpty()
    {
        return $this->warnIfEmpty;
    }

    /**
     * Return the number of columns (bootstrap) that the grid should use
     *
     * @return array
     */
    public function getToolbarSize(): array
    {
        return $this->toolbarSize;
    }

    /**
     * Return the columns to be displayed on the grid
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set the columns to be displayed, along with their data
     *
     * @return void
     * @throws \Exception
     */
    abstract public function setColumns();

    /**
     * The class of the grid table
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get an array of button instances to be rendered on the grid
     *
     * @param null $key
     * @return array
     */
    public function getButtons($key = null)
    {
        $buttons = $key ? $this->buttons[$key] : $this->buttons;

        // apply the position here
        return collect($buttons)->sortBy(function ($v) {
            return $v->position;
        })->toArray();
    }

    /**
     * Sets an array of buttons that would be rendered to the grid
     *
     * @return void
     */
    public function setButtons()
    {
        $this->setDefaultButtons();
    }

    /**
     * Get the data to be exported
     *
     * @return Collection
     */
    public function getExportData()
    {
        // work on the underlying query instance
        // this one has already passed through the filter
        $values = $this->getQuery()->take(static::$MAX_EXPORT_ROWS)->get();

        $columns = collect($this->getColumnsToExport())->reject(function ($v) {
            // reject all columns that have been set as not exportable
            return !$v->export;
        })->toArray();

        // map the results to the db query values
        $data = $values->map(function ($v) use ($columns) {
            $data = [];
            foreach ($columns as $column) {
                // render as per requested on each column
                // processRows() would have already taken care of processing the callbacks
                // so here, we only pass the required arguments
                if (is_callable($column->data)) {
                    array_push($data, [$column->name => call_user_func($column->data, $v, $column->key)]);
                } else {
                    array_push($data, [$column->name => $v->{$column->key}]);
                }
            }
            // collapse the data by a single level
            return collect($data)->collapse()->toArray();
        });

        return $data;
    }

    /**
     * Gets the columns to be exported
     *
     * @return array
     */
    public function getColumnsToExport()
    {
        return $this->getProcessedColumns();
    }

    /**
     * Get the processed columns
     *
     * @return array
     */
    public function getProcessedColumns(): array
    {
        return $this->processColumns();
    }

    /**
     * Return the view used to display the search form
     *
     * @return string
     */
    public function getSearchView(): string
    {
        return 'leantony::grid.search';
    }

    /**
     * Check parameters
     *
     * @return void
     */
    protected function checkParameters()
    {
        if (!$this->query instanceof Builder) {
            throw new InvalidArgumentException("The object of type query is invalid. Pass an instance of Illuminate\\Database\\Eloquent\\Builder");
        }
        if (!$this->request instanceof Request) {
            throw new InvalidArgumentException("The object of type request is invalid. Pass an instance of Illuminate\\Http\\Request");
        }
    }

    /**
     * Return a short name for the grid that can be used as a route identifier
     *
     * @return string
     */
    protected function shortSingularGridName(): string
    {
        return strtolower(Str::singular($this->getName()));
    }
}