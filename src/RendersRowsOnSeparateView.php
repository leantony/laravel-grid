<?php

namespace Leantony\Grid;

trait RendersRowsOnSeparateView
{
    /**
     * The variable that would be sent to a view to render the rows
     * If needed by any chance
     *
     * @return string
     */
    abstract public function getDataVariableAlias(): string;
}