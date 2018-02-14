<?php

namespace Leantony\Grid;

use Carbon\Carbon;
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
    use FiltersData,
        AddsRowFilters,
        ConfiguresButtons,
        ConfiguresRoutes;

    /**
     * The view that holds the loop data
     *
     * @var string
     */
    protected $rowsView;

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
     * The rows that appear on the table headers. Specified as $key => $value
     *
     * @var array
     */
    protected $rows = [];

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
    protected $processedRows = [];

    /**
     * The regxp pattern to be used to format the label names
     * All symbols and invalid characters would be ignored and replaced with a space
     *
     * @var string
     */
    protected $labelNamePattern = "/[^a-z0-9 -]+/";

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
     * Rows to be used as search
     *
     * @var array
     */
    protected $searchableRows = [];

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
        $this->setLinks();
        // default buttons on the grid
        $this->setButtons();
        // configuration to the buttons already set including adding new ones
        $this->configureButtons();
        // user defined rows
        $this->setRows();
        // data filters
        $this->executeFilters();
        // get filtered data
        $this->data = $this->getFilteredData();
    }

    /**
     * Update the links, if necessary
     *
     * Provide an array, with key value pairs
     * Key being the argument name and value being the route name
     *
     * @return $this
     */
    public function updateLinks()
    {
        $links = func_get_arg(0);

        if (!empty($links)) {
            foreach ($links as $k => $v) {
                $name = $k . 'RouteName';

                $this->{$name} = $v;
            }
        }
        return $this;
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
            'rows' => $this->processRows()
        ];
        return array_merge($data, $this->getExtraParams($params));
    }

    /**
     * Process the rows that were supplied
     *
     * @return array
     */
    public function processRows()
    {
        if (!empty($this->processedRows)) {
            return $this->processedRows;
        }
        $rows = [];
        // process
        foreach ($this->rows as $key => $value) {
            // check if we need to render this row
            if (isset($value['renderIf']) && is_callable($value['renderIf'])) {
                $func = $value['renderIf'];
                // when the callback returns false, then skip this row
                if (!$func($key)) {
                    continue;
                }
            }
            // css
            if (isset($value['styles'])) {
                $classAttributes = $value['styles'];
                $headerClass = $classAttributes['header'] ?? '';
                $rowClass = $classAttributes['row'] ?? 'col-md-2';
            } else {
                // by default, do not apply any styles on the <th> elements
                $headerClass = '';
                // by default, do not apply any styles on the values represented by each of the <th> elements
                $rowClass = '';
            }
            // set labels if not provided
            if (isset($value['label'])) {
                $name = $value['label'];
            } else {
                $name = ucwords(preg_replace($this->labelNamePattern, ' ', $key));
            }
            // note that this is only used to build a placeholder for the search field, and nothing else
            if (isset($value['searchable']) && $value['searchable'] === true) {
                $this->searchableRows[] = Str::lower($name);
            }
            // data for the row
            if (isset($value['data'])) {
                // note that this can also be a callback
                // as such it would be called on the grid view
                $data = $value['data'];
            } else {
                // check for a presenter
                if (isset($value['present'])) {
                    if (is_callable($value['present'])) {
                        // custom
                        $data = function ($item, $row) use ($value) {
                            return call_user_func($value['present'], $item, $row);
                        };
                    } else {
                        // laracasts presenter. call the function on the model instance
                        $data = function ($item, $row) use ($value) {
                            return $item->present()->{$value['present']};
                        };
                    }
                } else {
                    // format any dates
                    if (isset($value['date'])) {
                        $data = function ($item, $row) use ($value) {
                            return Carbon::parse($item->{$row})->format($value['dateFormat'] ?? 'Y-m-d');
                        };
                    } else {
                        $data = function ($item, $row) {
                            return $item->{$row};
                        };
                    }
                }
            }
            $filter = null;
            // add any filter
            if (isset($value['filter'])) {
                // a row can only have one filter
                $filter = $this->pushFilter($value, $key);
            }
            // once we are done, push to rows array
            array_push($rows, new Row([
                'name' => $name,
                'key' => $key,
                'data' => $data,
                'rowClass' => $rowClass,
                'headerClass' => $headerClass,
                'sortable' => $value['sort'] ?? false,
                'filter' => $filter,
                'raw' => $value['raw'] ?? false,
                'export' => $value['export'] ?? true,
            ]));
        }

        $this->processedRows = $rows;

        return $this->processedRows;
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
     * If the grid rows can be clicked on as links
     *
     * @return bool
     */
    public function allowsLinkableRows()
    {
        return $this->linkableRows;
    }

    /**
     * Specify if the default means of rendering the grid rows is to be skipped
     *
     * @return bool
     */
    public function skipsDefaultRowFormat()
    {
        return false;
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

        return view('leantony::grid.search', array_merge($data, $params))->render();
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
        if (empty($this->searchableRows)) {
            $placeholder = Str::plural(Str::slug($this->getName()));

            return sprintf('search %s ...', $placeholder);
        }

        $placeholder = collect($this->searchableRows)->implode(',');

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
     * Return the rows to be displayed on the grid
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Set the rows to be displayed
     *
     * @return void
     * @throws \Exception
     */
    abstract public function setRows();

    /**
     * The view that would render the rows
     *
     * @return string
     */
    public function getRowsView(): string
    {
        return $this->rowsView;
    }

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

        $rows = collect($this->getRowsToExport())->reject(function ($v) {
            // reject all rows that have been set as not exportable
            return !$v->export;
        })->toArray();

        // map the results to the db query values
        $data = $values->map(function ($v) use ($rows) {
            $data = [];
            foreach ($rows as $row) {
                // render as per requested on each row
                // processRows() would have already taken care of processing the callbacks
                // so here, we only pass the required arguments
                if (is_callable($row->data)) {
                    array_push($data, [$row->name => call_user_func($row->data, $v, $row->key)]);
                } else {
                    array_push($data, [$row->name => $v->{$row->key}]);
                }
            }
            // collapse the data by a single level
            return collect($data)->collapse()->toArray();
        });

        return $data;
    }

    /**
     * Gets the rows to be exported
     *
     * @return array
     */
    public function getRowsToExport()
    {
        return $this->getProcessedRows();
    }

    /**
     * Get the processed rows
     *
     * @return array
     */
    public function getProcessedRows(): array
    {
        return $this->processRows();
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