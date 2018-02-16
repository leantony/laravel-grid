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
}