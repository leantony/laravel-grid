<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Leantony\Grid\Buttons\GridButtonsInterface;
use Leantony\Grid\Buttons\RendersButtons;
use Leantony\Grid\Columns\CreatesColumns;
use Leantony\Grid\Columns\GridColumnsInterface;
use Leantony\Grid\Events\GridInitialized;
use Leantony\Grid\Events\UserActionRequested;
use Leantony\Grid\Filters\AddsColumnFilters;
use Leantony\Grid\Filters\GridFilterInterface;
use Leantony\Grid\Listeners\DataExportHandler;
use Leantony\Grid\Routing\ConfiguresRoutes;
use Leantony\Grid\Routing\GridRoutesInterface;

abstract class Grid implements Htmlable, GridInterface, GridButtonsInterface, GridFilterInterface, GridColumnsInterface, GridRoutesInterface
{
    use GridResources,
        CreatesColumns,
        ConfiguresRoutes,
        AddsColumnFilters,
        RendersButtons,
        RendersGrid;

    /**
     * Specify if the rows on the table should be clicked to navigate to the record
     *
     * @var bool
     */
    protected $linkableRows = false;

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
     * @var Paginator|Collection
     */
    protected $data;

    /**
     * An exporter instance to be used for export functionality
     *
     * @var DataExportHandler
     */
    protected $exportHandler = null;

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
     * Existing columns in the DB, to be used for validation of user requests
     *
     * @var array
     */
    protected $tableColumns = [];

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
        // initialized event
        event('grid.initialized', new GridInitialized($this, $params));
        // do filter, export, paginate, search = main user actions
        $result = event(
            'grid.fetch_data',
            new UserActionRequested($this, $this->getRequest(), $this->getQuery(), $this->tableColumns)
        );
        $this->setGridDataItems($result);

        return $this;
    }

    /**
     * Get the selected sort direction
     *
     * @param bool $opposite negate current existing parameter to ensure toggling
     * @return string the sort direction
     */
    public function getSelectedSortDirection($opposite = true): string
    {
        if ($selected = session('__grid.current_sort_direction')) {
            if ($opposite) {
                return $selected === 'asc' ? 'desc' : 'asc';
            }
            return $selected;
        }
        return 'asc';
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
        // table cols
        $this->tableColumns = $this->getTableColumns();
        // any links defined
        $this->setRoutes();
        // default buttons on the grid
        $this->setButtons();
        // configuration to the buttons already set including adding new ones. Even clearing all of them
        $this->configureButtons();
        // user defined columns
        $this->setColumns();
    }

    /**
     * Return a short name for the grid that can be used as a route identifier
     *
     * @return string
     */
    public function shortSingularGridName(): string
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
     * Get valid columns in the table
     *
     * @return array
     */
    public function getTableColumns()
    {
        if (empty($this->tableColumns)) {
            $cols = Schema::getColumnListing(call_user_func($this->getGridDatabaseTable()));
            $rejects = $this->getGridColumnsToSkipOnFilter();
            $this->tableColumns = collect($cols)->reject(function ($v) use ($rejects) {
                return in_array($v, $rejects);
            })->toArray();
        }
        return $this->tableColumns;
    }

    /**
     * The table name that is matched to the grid
     *
     * @return \Closure
     */
    public function getGridDatabaseTable()
    {
        $gridName = $this->name;
        return function () use ($gridName) {
            return Str::plural(Str::slug($gridName, '_'));
        };
    }

    /**
     * Set the columns to be displayed, along with their data
     *
     * @return void
     * @throws \Exception
     */
    abstract public function setColumns();

    /**
     * Set data variables for the grid
     * This will need to be passed on the the grid view so that they are displayed
     *
     * @param array $result
     * @return void
     */
    protected function setGridDataItems(array $result): void
    {
        $data = data_get($result, 0);
        if (is_array($data)) {
            // an export has been triggered
            $this->data = $data['data'];
            $this->exportHandler = $data['exporter'];
        } else {
            if ($data === null) {
                // revert to empty collection
                $this->data = collect([]);
            } else {
                $this->data = $data;
            }
        }
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
     * Return the ID of the grid
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
     * Get the data to be rendered on the grid
     *
     * @return Paginator|Collection
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
        return $this->data instanceof Paginator;
    }

    /**
     * Display a warning message if the grid has no data
     *
     * @return bool
     */
    public function warnIfEmpty()
    {
        return $this->gridShouldWarnIfEmpty();
    }

    /**
     * The class of the grid table
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->getGridDefaultClass();
    }

    /**
     * The class of the grid table header
     *
     * @return string
     */
    public function getHeaderClass(): string
    {
        return $this->getGridDefaultHeaderClass();
    }
}