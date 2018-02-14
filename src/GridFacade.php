<?php

namespace Leantony\Grid;

use Illuminate\Support\Facades\Facade;

class GridFacade extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'grid';
    }
}