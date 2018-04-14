<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Illuminate\Contracts\Support\Htmlable;

class GridToolbarSearch implements Htmlable
{
    /**
     * Size of the search column
     *
     * @var int
     */
    public $colSize = 6;

    /**
     * Where search will go to
     *
     * @var string
     */
    public $action = '#';

    /**
     * Placeholder
     *
     * @var string
     */
    public $placeholder = 'search for an item...';

    /**
     * GridToolbarSearch constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        foreach ($args as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function render()
    {
        $args = func_get_args();
        $data = $this->compactData($args);
        return view('leantony::grid.search', $data)->render();
    }

    /**
     * @param array $args
     * @return array
     */
    public function compactData($args)
    {
        $v = [
            'colSize' => $this->colSize,
            'action' => $this->action,
            'placeholder' => $this->placeholder
        ];

        return array_merge($v, $args);
    }
}