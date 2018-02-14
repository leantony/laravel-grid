<?php

namespace Leantony\Grid;

class Row
{
    /**
     * Row constructor.
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
     * @return null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new \InvalidArgumentException("The property " . $name . " was not found on this class");
    }

    /**
     * Dynamically set properties
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}