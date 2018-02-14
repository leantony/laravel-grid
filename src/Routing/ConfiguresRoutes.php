<?php

namespace Leantony\Grid;

use InvalidArgumentException;

trait ConfiguresRoutes
{
    /**
     * The route name used for sorting
     *
     * @var string
     */
    protected $sortRouteName = '';

    /**
     * The search route name
     *
     * @var string
     */
    protected $searchRoute = '#';

    /**
     * The index route
     *
     * @var string
     */
    protected $indexRouteName = '';

    /**
     * The view route
     *
     * @var string
     */
    protected $viewRouteName = '';

    /**
     * @var string
     */
    protected $deleteRouteName = '';

    /**
     * The create route name
     *
     * @var string
     */
    protected $createRouteName = '#';

    /**
     * Set the links to be used on the grid for the buttons and forms (filter and search)
     * Use route names for simplicity
     *
     * @return void
     */
    abstract public function setLinks();

    /**
     * The search route to be used for the search form
     *
     * @return string
     */
    public function getSearchRoute(): string
    {
        return route($this->searchRoute);
    }

    /**
     * Get the url used for sort
     *
     * @return string|callable
     */
    public function getSortUrl()
    {
        return function ($key) {
            return route($this->sortRouteName, add_query_param([$this->getSortParam() => $key]));
        };
    }

    /**
     * The index route link
     *
     * @return string
     */
    public function getIndexRouteLink(): string
    {
        return $this->getRouteLinkFor('index');
    }

    /**
     * Get the route link for any property named `xxxRouteName`
     *
     * @param string $name
     * @return string
     */
    public function getRouteLinkFor(string $name)
    {
        $prop = $name . 'RouteName';
        if (property_exists($this, $prop)) {
            return route($this->{$prop});
        }
        throw new InvalidArgumentException("The property with name " . $name . "does not exist on this class. Check the name. It should be " . $name . 'RouteName');
    }

    /**
     * The create Route
     *
     * @return string
     */
    public function getCreateRouteName(): string
    {
        if ($this->createRouteName == '#' || !$this->createRouteName) {
            return $this->createRouteName;
        }
        return route($this->createRouteName, add_query_param(['ref' => $this->getId()]));
    }
}