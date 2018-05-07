<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Events;

use Illuminate\Http\Request;
use Leantony\Grid\Grid;
use Leantony\Grid\GridInterface;

class UserActionRequested
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var
     */
    public $builder;

    /**
     * @var Grid|GridInterface
     */
    public $grid;

    /**
     * @var array
     */
    public $validTableColumns;

    /**
     * @var array
     */
    public $args;

    /**
     * UserActionRequested constructor.
     * @param GridInterface $grid
     * @param Request $request
     * @param $builder
     * @param $validTableColumns
     * @param mixed ...$args
     */
    public function __construct(GridInterface $grid, Request $request, $builder, $validTableColumns, ...$args)
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->builder = $builder;
        $this->validTableColumns = $validTableColumns;
        $this->args = array_collapse($args);
    }
}