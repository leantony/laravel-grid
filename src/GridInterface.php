<?php

namespace Leantony\Grid;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface GridInterface
{
    /**
     * Create the grid
     *
     * @param array $params
     * @return GridInterface
     */
    public function create(array $params): GridInterface;

    /**
     * Set the links to be used on the grid for the buttons and forms (filter and search)
     * Use route names for simplicity
     *
     * @return void
     */
    public function setRoutes();

    /**
     * Filename for export
     *
     * @return string
     */
    public function getFileNameForExport();

    /**
     * Download export data
     *
     * @param string $type
     */
    public function downloadExportedAs($type = 'xlsx');

    /**
     * Execute all filters
     *
     * @return void
     */
    public function executeFilters();

    /**
     * Paginate the filtered data
     *
     * @return void
     */
    public function paginate();

    /**
     * Search the rows
     *
     * @return void
     */
    public function searchRows();

    /**
     * Filter the grid rows
     *
     * @return void
     */
    public function filterRows();

    /**
     * The table name to be sorted
     *
     * @return \Closure
     */
    public function getSortTable();

    /**
     * Sort a query builder
     *
     * @return void
     */
    public function sort();

    /**
     * Get the filtered data
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredData();

    /**
     * Initialize grid variables
     *
     * @return void
     */
    public function init();

    /**
     * Get the data
     *
     * @return Paginator|LengthAwarePaginator|Collection|array
     */
    public function getData();

    /**
     * Render the grid
     *
     * @return string
     */
    public function render();

    /**
     * Process the rows that were supplied
     *
     * @return array
     */
    public function processColumns();

    /**
     * Return the ID of the grid
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Override this method and return a callback so that linkable rows are applied
     *
     * @return Closure
     * @throws \Exception
     */
    public function getLinkableCallback(): Closure;

    /**
     * If the grid rows can be clicked on as links
     *
     * @return bool
     */
    public function allowsLinkableRows();

    /**
     * Render the search form on the grid
     *
     * @return string
     */
    public function getSearch();

    /**
     * Get the name of the grid. Can be the table name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Return the rows to be displayed on the grid
     *
     * @return array
     */
    public function getColumns(): array;

    /**
     * Set the columns to be displayed
     *
     * @return void
     * @throws \Exception
     */
    public function setColumns();

    /**
     * The HTML class/classes of the grid table
     *
     * @return string
     */
    public function getClass(): string;

    /**
     * Get an array of button instances to be rendered on the grid
     *
     * @param null $key
     * @return array
     */
    public function getButtons($key = null);

    /**
     * Sets an array of buttons that would be rendered to the grid
     *
     * @return void
     */
    public function setButtons();

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

    /**
     * Get the processed rows
     *
     * @return array
     */
    public function getProcessedColumns(): array;
}