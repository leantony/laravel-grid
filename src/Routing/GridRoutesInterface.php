<?php

namespace Leantony\Grid\Routing;

interface GridRoutesInterface
{
    /**
     * Set the links to be used on the grid for the buttons and forms (filter and search)
     * Use route names for simplicity
     *
     * @return void
     */
    public function setRoutes();

}