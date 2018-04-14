<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Columns;

use InvalidArgumentException;

class Column
{
    /**
     * Column constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $k => $v) {
            $this->__set($k, $v);
        }
    }

    /**
     * Dynamically get properties
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new InvalidArgumentException("The property " . $name . " was not found on this class");
    }

    /**
     * Dynamically set properties
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}