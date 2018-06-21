<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface GridInterface
{
    /**
     * Create the grid
     *
     * @param array $params
     * @return $this
     */
    public function create(array $params): self;

    /**
     * Initialize grid variables
     *
     * @return void
     */
    public function init();

    /**
     * Get the data
     *
     * @return Paginator|Collection|array
     */
    public function getData();

    /**
     * An implicit call from the __toString() method. Passes in the grid data to the actual view that we have created
     * so that it can be rendered
     *
     * @return string
     */
    public function render();

    /**
     * Render the search form on the grid
     *
     * @return string
     * @throws \Throwable
     */
    public function renderSearchForm();

    /**
     * Pass the grid on to the user defined view e.g an index page, along with any data that may be required
     * Will dynamically switch between displaying the grid and downloading exported files
     *
     * @param string $viewName the view name
     * @param array $data any extra data to be sent to the view
     * @param string $as the variable to be sent to the view, representing the grid
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function renderOn(string $viewName, $data = [], $as = 'grid');

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
     * Get the name of the grid. Can be the table name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * The HTML class/classes of the grid table
     *
     * @return string
     */
    public function getClass(): string;

    /**
     * Returns a closure that will be executed to apply a class for each row on the grid
     * The closure takes two arguments - `name` of grid, and `item` being iterated upon
     *
     * @return Closure
     */
    public function getRowCssStyle(): Closure;
}