<?php

namespace Leantony\Grid;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Leantony\Grid\Buttons\GridButtonsInterface;
use Leantony\Grid\Buttons\RendersButtons;
use Leantony\Grid\Columns\CreatesColumns;
use Leantony\Grid\Columns\GridColumnsInterface;
use Leantony\Grid\Filters\AddsColumnFilters;
use Leantony\Grid\Filters\FiltersSearchesThenExportsData;
use Leantony\Grid\Filters\GridDataExportInterface;
use Leantony\Grid\Filters\GridFilterInterface;
use Leantony\Grid\Routing\ConfiguresRoutes;
use Leantony\Grid\Routing\GridRoutesInterface;

abstract class Grid implements Htmlable, GridInterface, GridButtonsInterface, GridFilterInterface, GridDataExportInterface, GridColumnsInterface, GridRoutesInterface
{
    use CreatesColumns,
        ConfiguresRoutes,
        AddsColumnFilters,
        RendersButtons,
        FiltersSearchesThenExportsData;

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
     * The id of the grid. Many grids can exist on the same page, but the ID has to be unique
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
     * Extra parameters sent to the grid's blade view
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
     * The toolbar size. 6 columns on the right and 6 on the left
     * Left holds the search bar, while the right part holds the buttons
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
     * Short singular name for the grid
     *
     * @var string
     */
    protected $shortSingularName;

    /**
     * Short grid identifier, to be used for route param names
     *
     * @var string
     */
    protected $shortGridIdentifier;

    /**
     * Create the grid
     *
     * @param array $params
     * @return GridInterface
     * @throws \Exception
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
     * @throws \Exception
     */
    public function init()
    {
        // the grid ID
        $this->id = Str::singular(Str::camel($this->name)) . '-' . 'grid';
        // short singular name
        $this->shortSingularName = $this->shortSingularGridName();
        // short grid identifier
        $this->shortGridIdentifier = $this->transformName();
        // any links defined
        $this->setRoutes();
        // default buttons on the grid
        $this->setButtons();
        // configuration to the buttons already set including adding new ones. Even clearing all of them
        $this->configureButtons();
        // user defined columns
        $this->setColumns();
        // data filters
        $this->executeFilters();
        // get filtered data
        $this->data = $this->getFilteredData();
    }

    /**
     * Return a short name for the grid that can be used as a route identifier
     *
     * @return string
     */
    protected function shortSingularGridName(): string
    {
        if ($this->shortSingularName === null) {
            $this->shortSingularName = strtolower(Str::singular($this->getName()));
        }
        return $this->shortSingularName;
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
        if ($this->shortGridIdentifier === null) {
            return Str::slug(Str::singular($this->getName()), '_');
        }
        return $this->shortGridIdentifier;
    }

    /**
     * Set the columns to be displayed, along with their data
     *
     * @return void
     * @throws \Exception
     */
    abstract public function setColumns();

    /**
     * Get the data to be rendered on the grid
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
     * @throws \Throwable
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     * @throws \Throwable
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Render the grid as HTML on the user defined view
     *
     * @return string
     * @throws \Throwable
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
     * Returns a closure that will be executed to apply a class for each row on the grid
     * The closure takes two arguments - `name` of grid, and `item` being iterated upon
     *
     * @return Closure
     */
    abstract public function getRowCssStyle(): Closure;

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
        return $this->data instanceof LengthAwarePaginator;
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
     * The class of the grid table
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Render the grid on a user defined view
     *
     * @param string $viewName
     * @param array $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderOn(string $viewName, $data = [])
    {
        if ($this->wantsToExport()) {
            return $this->export();
        }
        return view($viewName, array_merge($data, ['grid' => $this]));
    }

    /**
     * Check parameters
     *
     * @return void
     */
    protected function checkParameters()
    {
        if (!$this->getQuery() instanceof \Illuminate\Database\Query\Builder
            || !$this->getQuery() instanceof \Illuminate\Database\Eloquent\Builder) {
            throw new InvalidArgumentException("The object of type query is invalid. 
            You need to pass an instance of Illuminate\\Database\\Eloquent\\Builder or Illuminate\\Database\\Query\\Builder");
        }
        if (!$this->request instanceof Request) {
            throw new InvalidArgumentException("The object of type request is invalid. You need to pass an instance of Illuminate\\Http\\Request");
        }
    }
}