<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void start(array $data)
 * @method static void end()
 */
class Modal extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'modal';
    }
}